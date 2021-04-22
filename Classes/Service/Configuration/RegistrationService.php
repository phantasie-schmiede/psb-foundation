<?php
/** @noinspection UnsupportedStringOffsetOperationsInspection */
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace PSB\PsbFoundation\Service\Configuration;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\IndexedReader;
use InvalidArgumentException;
use JsonException;
use PSB\PsbFoundation\Annotation\AjaxPageType;
use PSB\PsbFoundation\Annotation\ModuleAction;
use PSB\PsbFoundation\Annotation\ModuleConfig;
use PSB\PsbFoundation\Annotation\PluginAction;
use PSB\PsbFoundation\Annotation\PluginConfig;
use PSB\PsbFoundation\Controller\Backend\AbstractModuleController;
use PSB\PsbFoundation\Data\ExtensionInformationInterface;
use PSB\PsbFoundation\Service\ExtensionInformationService;
use PSB\PsbFoundation\Service\LocalizationService;
use PSB\PsbFoundation\Utility\ArrayUtility;
use PSB\PsbFoundation\Utility\ContextUtility;
use PSB\PsbFoundation\Utility\StringUtility;
use PSB\PsbFoundation\Utility\TypoScript\PageObjectConfiguration;
use PSB\PsbFoundation\Utility\TypoScript\TypoScriptUtility;
use PSB\PsbFoundation\Utility\ValidationUtility;
use ReflectionClass;
use RuntimeException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ArrayUtility as Typo3CoreArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

/**
 * Class RegistrationService
 *
 * @package PSB\PsbFoundation\Service\Configuration
 */
class RegistrationService
{
    public const PAGE_TYPE_REGISTRATION_MODES = [
        'EXT_TABLES'   => 'ext_tables',
        'TCA_OVERRIDE' => 'tca_override',
    ];

    private const COLLECT_MODES = [
        'CONFIGURE_PLUGINS' => 'configurePlugins',
        'REGISTER_MODULES'  => 'registerModules',
        'REGISTER_PLUGINS'  => 'registerPlugins',
    ];

    /**
     * This static variable is used to keep track of already registered wizard groups and is pre-filled with TYPO3's
     * default groups as defined in
     * typo3\sysext\backend\Configuration\TSConfig\Page\Mod\Wizards\NewContentElement.tsconfig
     *
     * @var string[]
     */
    private array $contentElementWizardGroups = [
        'common',
        'forms',
        'menu',
        'plugins',
        'special',
    ];

    /**
     * For use in ext_localconf.php files
     *
     * @param string      $extensionKey
     * @param string      $group
     * @param string      $pluginName
     * @param string|null $iconIdentifier
     *
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws JsonException
     */
    public function addPluginToElementWizard(
        string $extensionKey,
        string $group,
        string $pluginName,
        string $iconIdentifier = null
    ): void {
        $iconIdentifier = $iconIdentifier ?? $extensionKey . '-' . str_replace('_', '-',
                GeneralUtility::camelCaseToLowerCaseUnderscored($pluginName));
        $ll = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TSConfig/Page/wizard.xlf:' . $group . '.elements.' . lcfirst($pluginName);
        $description = $ll . '.description';
        $title = $ll . '.title';
        $listType = str_replace('_', '', $extensionKey) . '_' . mb_strtolower($pluginName);
        $localizationService = GeneralUtility::makeInstance(LocalizationService::class);

        if (false === $localizationService->translationExists($description)) {
            $description = '';
        }

        if (false === $localizationService->translationExists($title)) {
            $title = $pluginName;
        }

        $configuration = [
            'description'          => $description,
            'iconIdentifier'       => GeneralUtility::makeInstance(IconRegistry::class)
                ->isRegistered($iconIdentifier) ? $iconIdentifier : 'content-plugin',
            'title'                => $title,
            'tt_content_defValues' => [
                'CType'     => 'list',
                'list_type' => $listType,
            ],
        ];

        $this->addElementWizardItem($configuration, $extensionKey, $group, $listType);
    }

    /**
     * For use in ext_localconf.php files
     *
     * @param string      $contentType
     * @param string      $extensionKey
     * @param string      $group
     * @param string|null $iconIdentifier
     * @param string|null $templatePath
     *
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws JsonException
     */
    public function configureContentType(
        string $contentType,
        string $extensionKey,
        string $group,
        string $iconIdentifier = null,
        string $templatePath = null
    ): void {
        $internalContentType = $this->buildContentTypeKey($extensionKey, $contentType);
        $ll = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TSConfig/Page/wizard.xlf:' . $group . '.elements.' . $contentType;

        $localizationService = GeneralUtility::makeInstance(LocalizationService::class);
        $localizationService->translationExists($ll . '.description');
        $localizationService->translationExists($ll . '.title');

        $configuration = [
            'description'          => $ll . '.description',
            'iconIdentifier'       => $iconIdentifier ?? $contentType,
            'title'                => $ll . '.title',
            'tt_content_defValues' => [
                'CType' => $internalContentType,
            ],
        ];

        $this->addElementWizardItem($configuration, $extensionKey, $group, $internalContentType);

        $directory = 'EXT:' . $extensionKey . '/Resources/Private/Templates/Content/';
        $fileName = GeneralUtility::underscoredToUpperCamelCase($contentType) . '.html';
        $previewTemplate = GeneralUtility::getFileAbsFileName($directory . 'Preview/' . $fileName);

        if (is_file($previewTemplate)) {
            $pageTS['mod']['web_layout']['tt_content']['preview'][$internalContentType] = $previewTemplate;
            ExtensionManagementUtility::addPageTSConfig(TypoScriptUtility::convertArrayToTypoScript($pageTS));
        }

        $typoScript['tt_content'][$internalContentType] = [
            TypoScriptUtility::TYPO_SCRIPT_KEYS['OBJECT_TYPE'] => 'FLUIDTEMPLATE',
            'file'                                             => $templatePath ?? $directory . $fileName,
        ];

        ExtensionManagementUtility::addTypoScriptSetup(TypoScriptUtility::convertArrayToTypoScript($typoScript));
    }

    /**
     * For use in ext_localconf.php files
     *
     * @param ExtensionInformationInterface $extensionInformation
     *
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws JsonException
     */
    public function configurePlugins(ExtensionInformationInterface $extensionInformation): void
    {
        if (is_iterable($extensionInformation->getPlugins())) {
            foreach ($extensionInformation->getPlugins() as $pluginName => $controllerClassNames) {
                if (is_iterable($controllerClassNames)) {
                    [
                        $pluginConfiguration,
                        $controllersAndCachedActions,
                        $controllersAndUncachedActions,
                    ] = $this->collectActionsAndConfiguration($controllerClassNames,
                        self::COLLECT_MODES['CONFIGURE_PLUGINS'], $pluginName);

                    ExtensionUtility::configurePlugin(
                        $extensionInformation->getExtensionName(),
                        $pluginName,
                        $controllersAndCachedActions,
                        $controllersAndUncachedActions
                    );

                    if (isset($pluginConfiguration[PluginConfig::class])) {
                        /** @var PluginConfig $pluginConfig */
                        $pluginConfig = $pluginConfiguration[PluginConfig::class];
                        $group = $pluginConfig->getGroup();
                        $iconIdentifier = $pluginConfig->getIconIdentifier();
                    }

                    $this->addPluginToElementWizard($extensionInformation->getExtensionKey(),
                        $group ?? mb_strtolower($extensionInformation->getVendorName()),
                        $pluginName,
                        $iconIdentifier ?? null);
                }
            }
        }
    }

    /**
     * For use in Configuration/TCA/Overrides/tt_content.php files
     *
     * @param string      $extensionKey
     * @param string      $group
     * @param string      $key
     * @param string      $position Format is "position:key". Possible values for position are after, before and in.
     *                              Example: "before:text"
     * @param string|null $iconIdentifier
     * @param string|null $title
     *
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws JsonException
     */
    public function registerContentType(
        string $extensionKey,
        string $group,
        string $key,
        string $position,
        string $iconIdentifier = null,
        string $title = null
    ): void {
        if (!isset($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'])) {
            throw new RuntimeException(__CLASS__ . ': TCA is not available yet!', 1553261710);
        }

        $internalKey = $this->buildContentTypeKey($extensionKey, $key);
        $localizationService = GeneralUtility::makeInstance(LocalizationService::class);

        if (null === $title) {
            $title = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TSConfig/Page/wizard.xlf:' . $group . '.elements.' . $key . '.title';
            $localizationService->translationExists($title);
        }

        ExtensionManagementUtility::addPlugin(
            [
                $title,
                $internalKey,
                $iconIdentifier ?? $key,
            ],
            'CType',
            $extensionKey
        );

        $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$internalKey] = $iconIdentifier ?? $key;

        $activeGroup = 'standard';
        $contentTypeGroups = [];
        $groupLabels = [];
        $newContentTypeConfiguration = [];

        foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $contentTypeConfiguration) {
            switch ($contentTypeConfiguration[1]) {
                case '--div--':
                    $labelParts = explode('.', $contentTypeConfiguration[0]);
                    $activeGroup = array_pop($labelParts);
                    $groupLabels[$activeGroup] = $contentTypeConfiguration[0];
                    break;
                case $internalKey:
                    $newContentTypeConfiguration = $contentTypeConfiguration;
                    break;
                default:
                    $contentTypeGroups[$activeGroup][] = $contentTypeConfiguration;
            }
        }

        [$operator, $target] = explode(':', $position);

        if ('in' === $operator) {
            $contentTypeGroups[$target][] = $newContentTypeConfiguration;
        } else {
            [$group, $index] = explode('.',
                array_search($target, Typo3CoreArrayUtility::flatten($contentTypeGroups), true));

            if ('after' === $operator) {
                $index++;
            }

            $contentTypeGroups[$group] = ArrayUtility::insertIntoArray($contentTypeGroups[$group],
                [$newContentTypeConfiguration], $index);
        }

        $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] = [];

        foreach ($contentTypeGroups as $groupKey => $groupElements) {
            if (isset($groupLabels[$groupKey]) && !empty($groupLabels[$groupKey])) {
                $groupKey = $groupLabels[$groupKey];
            } else {
                $groupKey = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TSConfig/Page/wizard.xlf:' . $groupKey . '.header';
                $localizationService->translationExists($groupKey);
            }

            $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'][] = [
                $groupKey,
                '--div--',
            ];

            array_push($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'], ...$groupElements);
        }

        $file = GeneralUtility::getFileAbsFileName('EXT:' . $extensionKey . '/Configuration/TCA/Content/' . $key . '.php');

        if (is_file($file)) {
            /** @noinspection PhpIncludeInspection */
            $tcaOfContentType = require $file;

            if (is_array($tcaOfContentType)) {
                $coreFields = [];
                $contentTypeFields = [];

                foreach ($tcaOfContentType as $field => $configuration) {
                    if (!isset($GLOBALS['TCA']['tt_content']['columns'][$field])) {
                        $contentTypeCondition = 'FIELD:CType:=:' . $internalKey;
                        if (isset($configuration['displayCond'])) {
                            $configuration['displayCond'] = [
                                'AND' => [
                                    $contentTypeCondition,
                                    $configuration['displayCond'],
                                ],
                            ];
                        } else {
                            $configuration['displayCond'] = $contentTypeCondition;
                        }

                        $GLOBALS['TCA']['tt_content']['columns'][$field] = $configuration;
                        $contentTypeFields[] = $field;
                    } else {
                        $coreFields[] = $field;
                    }
                }

                $elementTitle = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TSConfig/Page/wizard.xlf:' . $group . '.elements.' . $key . '.title';

                $showItems = [
                    '--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xml:palette.general;general',
                    implode(',', $coreFields ?? []),
                    '--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xml:tabs.access',
                    '--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xml:palette.visibility;visibility',
                    '--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xml:palette.access;access',
                    '--div--;' . $elementTitle,
                    implode(',', $contentTypeFields ?? []),
                ];

                $GLOBALS['TCA']['tt_content']['types'][$internalKey] = [
                    'showitem' => implode(',', $showItems),
                ];
            }
        }
    }

    /**
     * For use in ext_tables.php files
     *
     * @param ExtensionInformationInterface $extensionInformation
     *
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws JsonException
     */
    public function registerModules(ExtensionInformationInterface $extensionInformation): void
    {
        if (!ContextUtility::isBackend() || !is_iterable($extensionInformation->getModules())) {
            return;
        }

        $localizationService = GeneralUtility::makeInstance(LocalizationService::class);

        foreach ($extensionInformation->getModules() as $submoduleKey => $controllerClassNames) {
            if (is_iterable($controllerClassNames)) {
                [
                    $moduleConfiguration,
                    $controllersAndActions,
                ] = $this->collectActionsAndConfiguration($controllerClassNames,
                    self::COLLECT_MODES['REGISTER_MODULES']);

                if (isset($moduleConfiguration[ModuleConfig::class])) {
                    /** @var ModuleConfig $moduleConfig */
                    $moduleConfig = $moduleConfiguration[ModuleConfig::class];
                    $iconIdentifier = $moduleConfig->getIconIdentifier();
                    $mainModuleName = $moduleConfig->getMainModuleName();
                    $position = $moduleConfig->getPosition();
                    $access = $moduleConfig->getAccess();
                    $icon = $moduleConfig->getIcon();
                    $labels = $moduleConfig->getLabels();
                    $navigationComponentId = $moduleConfig->getNavigationComponentId();
                }

                $iconIdentifier = $iconIdentifier ?? 'module-' . $submoduleKey;

                if (!isset($labels)) {
                    $labels = 'LLL:EXT:' . $extensionInformation->getExtensionKey() . '/Resources/Private/Language/Backend/Modules/' . $submoduleKey . '.xlf';
                }

                $localizationService->translationExists($labels . ':mlang_labels_tabdescr');
                $localizationService->translationExists($labels . ':mlang_labels_tablabel');
                $localizationService->translationExists($labels . ':mlang_tabs_tab');

                ExtensionUtility::registerModule(
                    $extensionInformation->getExtensionName(),
                    $mainModuleName ?? 'web',
                    $submoduleKey,
                    $position ?? '',
                    $controllersAndActions,
                    [
                        'access'                => $access ?? 'group, user',
                        'icon'                  => $icon ?? null,
                        'iconIdentifier'        => GeneralUtility::makeInstance(IconRegistry::class)
                            ->isRegistered($iconIdentifier) ? $iconIdentifier : 'content-plugin',
                        'labels'                => $labels,
                        'navigationComponentId' => $navigationComponentId ?? null,
                    ]
                );
            }
        }
    }

    /**
     * @param ExtensionInformationInterface $extensionInformation
     * @param string                        $mode
     *
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws JsonException
     */
    public function registerPageTypes(ExtensionInformationInterface $extensionInformation, string $mode): void
    {
        ValidationUtility::checkValueAgainstConstant(self::PAGE_TYPE_REGISTRATION_MODES, $mode);
        $pageTypes = $extensionInformation->getPageTypes();

        if (empty($pageTypes)) {
            return;
        }

        foreach ($pageTypes as $doktype => $configuration) {
            if (self::PAGE_TYPE_REGISTRATION_MODES['EXT_TABLES'] === $mode) {
                $this->addPageTypeToGlobals($doktype, $configuration['allowedTables'] ?? ['*'],
                    $configuration['type'] ?? 'web');
            } else {
                $this->addPageTypeToPagesTca($doktype, $extensionInformation->getExtensionKey(),
                    $configuration['iconIdentifier'] ?? ('pageType-' . $configuration['name']), $configuration['name']);
            }
        }
    }

    /**
     * For use in Configuration/TCA/Overrides/tt_content.php files
     *
     * @param ExtensionInformationInterface $extensionInformation
     *
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws JsonException
     */
    public function registerPlugins(ExtensionInformationInterface $extensionInformation): void
    {
        if (!is_iterable($extensionInformation->getPlugins())) {
            return;
        }

        $localizationService = GeneralUtility::makeInstance(LocalizationService::class);

        foreach ($extensionInformation->getPlugins() as $pluginName => $controllerClassNames) {
            if (is_iterable($controllerClassNames)) {
                [$pluginConfiguration] = $this->collectActionsAndConfiguration($controllerClassNames,
                    self::COLLECT_MODES['REGISTER_PLUGINS']);

                if (isset($pluginConfiguration[PluginConfig::class])) {
                    /** @var PluginConfig $pluginConfig */
                    $pluginConfig = $pluginConfiguration[PluginConfig::class];
                    $title = $pluginConfig->getTitle();
                }

                if (!isset($title)) {
                    $title = 'LLL:EXT:' . $extensionInformation->getExtensionKey() . '/Resources/Private/Language/Backend/Configuration/TCA/Overrides/tt_content.xlf:plugin.' . $pluginName . '.title';
                    $localizationService->translationExists($title);
                }

                $iconIdentifier = $extensionInformation->getExtensionKey() . '-' . str_replace('_', '-',
                        GeneralUtility::camelCaseToLowerCaseUnderscored($pluginName));

                ExtensionUtility::registerPlugin(
                    $extensionInformation->getExtensionName(),
                    $pluginName,
                    $title,
                    GeneralUtility::makeInstance(IconRegistry::class)
                        ->isRegistered($iconIdentifier) ? $iconIdentifier : 'content-plugin'
                );
            }

            unset ($title);
        }
    }

    /**
     * @param string $extensionKey
     * @param string $key
     *
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws JsonException
     */
    private function addElementWizardGroup(string $extensionKey, string $key): void
    {
        $header = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TSConfig/Page/wizard.xlf:' . $key . '.header';
        GeneralUtility::makeInstance(LocalizationService::class)->translationExists($header);
        $pageTS['mod']['wizards']['newContentElement']['wizardItems'][$key] = [
            'header' => $header,
            'show'   => '*',
        ];

        ExtensionManagementUtility::addPageTSConfig(TypoScriptUtility::convertArrayToTypoScript($pageTS));
        $this->contentElementWizardGroups[] = $key;
    }

    /**
     * @param array  $configuration
     * @param string $extensionKey
     * @param string $group
     * @param string $key
     *
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws JsonException
     */
    private function addElementWizardItem(
        array $configuration,
        string $extensionKey,
        string $group,
        string $key
    ): void {
        if (!in_array($group, $this->contentElementWizardGroups, true)) {
            $this->addElementWizardGroup($extensionKey, $group);
        }

        $newPageTS['mod']['wizards']['newContentElement']['wizardItems'][$group]['elements'][$key] = $configuration;
        ExtensionManagementUtility::addPageTSConfig(TypoScriptUtility::convertArrayToTypoScript($newPageTS));
    }

    /**
     * @param int            $doktype
     * @param array|string[] $allowedTables
     * @param string         $type
     */
    private function addPageTypeToGlobals(int $doktype, array $allowedTables = ['*'], string $type = 'web'): void
    {
        // Add new page type:
        $GLOBALS['PAGES_TYPES'][$doktype] = [
            'type'          => $type,
            'allowedTables' => implode(',', $allowedTables),
        ];

        // Allow backend users to drag and drop the new page type:
        ExtensionManagementUtility::addUserTSConfig(
            'options.pageTree.doktypesToShowInNewPageDragArea := addToList(' . $doktype . ')'
        );
    }

    /**
     * @param int    $doktype
     * @param string $extensionKey
     * @param string $iconIdentifier
     * @param string $name
     *
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws JsonException
     */
    private function addPageTypeToPagesTca(
        int $doktype,
        string $extensionKey,
        string $iconIdentifier,
        string $name
    ): void {
        $table = 'pages';
        $label = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TCA/Overrides/pages.xlf:pageType.' . $name;
        GeneralUtility::makeInstance(LocalizationService::class)->translationExists($label);

        // Add new page type as possible select item:
        ExtensionManagementUtility::addTcaSelectItem(
            $table,
            'doktype',
            [
                $label,
                $doktype,
                'EXT:' . $extensionKey . '/Resources/Public/Icons/' . $iconIdentifier . '.svg',
            ],
            '1',
            'after'
        );

        Typo3CoreArrayUtility::mergeRecursiveWithOverrule(
            $GLOBALS['TCA'][$table],
            [
                // add icon for new page type:
                'ctrl'  => [
                    'typeicon_classes' => [
                        $doktype => $iconIdentifier,
                    ],
                ],
                // add all page standard fields and tabs to your new page type
                'types' => [
                    (string)$doktype => [
                        'showitem' => $GLOBALS['TCA'][$table]['types'][PageRepository::DOKTYPE_DEFAULT]['showitem'],
                    ],
                ],
            ]
        );
    }

    /**
     * @param string $extensionKey
     * @param string $contentType
     *
     * @return string
     */
    private function buildContentTypeKey(string $extensionKey, string $contentType): string
    {
        return mb_strtolower(str_replace('_', '', $extensionKey) . '_' . $contentType);
    }

    /**
     * @param array  $controllerClassNames
     * @param string $collectMode
     * @param string $pluginName
     *
     * @return array
     */
    private function collectActionsAndConfiguration(
        array $controllerClassNames,
        string $collectMode,
        string $pluginName = ''
    ): array {
        $configuration = [];
        $controllersAndCachedActions = [];
        $controllersAndUncachedActions = [];
        $annotationReader = new IndexedReader(new AnnotationReader());
        $extensionInformationService = GeneralUtility::makeInstance(ExtensionInformationService::class);

        foreach ($controllerClassNames as $controllerClassName) {
            $controller = GeneralUtility::makeInstance(ReflectionClass::class, $controllerClassName);
            $controllersAndCachedActions[$controllerClassName] = [];

            if (self::COLLECT_MODES['REGISTER_PLUGINS'] !== $collectMode) {
                $methods = $controller->getMethods();

                foreach ($methods as $method) {
                    $methodName = $method->getName();

                    if (!StringUtility::endsWith($methodName, 'Action')
                        || StringUtility::beginsWith($methodName, 'initialize')
                        || in_array($method->getDeclaringClass()->getName(),
                            [AbstractModuleController::class, ActionController::class], true)
                    ) {
                        continue;
                    }

                    /** @var ModuleAction|null $moduleAction */
                    $docComment = $annotationReader->getMethodAnnotations($method);

                    if (isset($docComment[ModuleAction::class])) {
                        /** @var ModuleAction $action */
                        $action = $docComment[ModuleAction::class];

                        $actionName = mb_substr($methodName, 0, -6);

                        if (true === $action->isDefault()) {
                            array_unshift($controllersAndCachedActions[$controllerClassName],
                                $actionName);
                        } else {
                            $controllersAndCachedActions[$controllerClassName][] = $actionName;
                        }
                    }

                    if (isset($docComment[PluginAction::class])) {
                        /** @var PluginAction $action */
                        $action = $docComment[PluginAction::class];

                        $actionName = mb_substr($methodName, 0, -6);

                        if (true === $action->isDefault()) {
                            array_unshift($controllersAndCachedActions[$controllerClassName],
                                $actionName);
                        } else {
                            $controllersAndCachedActions[$controllerClassName][] = $actionName;
                        }

                        if (self::COLLECT_MODES['CONFIGURE_PLUGINS'] === $collectMode
                            && true === $action->isUncached()) {
                            $controllersAndUncachedActions[$controllerClassName][] = $actionName;
                        }

                        if (isset($docComment[AjaxPageType::class])) {
                            $extensionInformation = $extensionInformationService->extractExtensionInformationFromClassName($controllerClassName);
                            /** @var AjaxPageType $ajaxPageType */
                            $ajaxPageType = $docComment[AjaxPageType::class];
                            $pageObjectConfiguration = GeneralUtility::makeInstance(PageObjectConfiguration::class);
                            $pageObjectConfiguration->setAction($actionName);
                            $pageObjectConfiguration->setCacheable($ajaxPageType->isCacheable());
                            $pageObjectConfiguration->setContentType($ajaxPageType->getContentType());
                            $controllerName = $extensionInformationService->convertControllerClassToBaseName($controllerClassName);
                            $pageObjectConfiguration->setController($controllerName);
                            $pageObjectConfiguration->setDisableAllHeaderCode($ajaxPageType->isDisableAllHeaderCode());
                            $pageObjectConfiguration->setExtensionName($extensionInformation['extensionName']);
                            $pageObjectConfiguration->setPluginName($pluginName);
                            $pageObjectConfiguration->setTypeNum($ajaxPageType->getTypeNum());
                            $typoScriptObjectName = strtolower(implode('_', [
                                'ajax',
                                $extensionInformation['vendorName'],
                                $extensionInformation['extensionName'],
                                $controllerName,
                                $actionName,
                            ]));
                            $pageObjectConfiguration->setTypoScriptObjectName($typoScriptObjectName);
                            $pageObjectConfiguration->setVendorName($extensionInformation['vendorName']);
                            TypoScriptUtility::registerAjaxPageType($pageObjectConfiguration);
                        }
                    }
                }
            }

            if (self::COLLECT_MODES['CONFIGURE_PLUGINS'] !== $collectMode) {
                Typo3CoreArrayUtility::mergeRecursiveWithOverrule($configuration,
                    $annotationReader->getClassAnnotations($controller));
            }
        }

        if (self::COLLECT_MODES['REGISTER_PLUGINS'] !== $collectMode) {
            array_walk($controllersAndCachedActions, static function (&$value) {
                $value = implode(', ', $value);
            });

            if (self::COLLECT_MODES['CONFIGURE_PLUGINS'] === $collectMode) {
                array_walk($controllersAndUncachedActions, static function (&$value) {
                    $value = implode(', ', $value);
                });
            }
        }

        switch ($collectMode) {
            case self::COLLECT_MODES['CONFIGURE_PLUGINS']:
                return [$configuration, $controllersAndCachedActions, $controllersAndUncachedActions];
            case self::COLLECT_MODES['REGISTER_MODULES']:
                return [$configuration, $controllersAndCachedActions];
            case self::COLLECT_MODES['REGISTER_PLUGINS']:
                return [$configuration];
            default:
                throw new InvalidArgumentException(__CLASS__ . ': $collectMode has to be a value defined in the constant COLLECT_MODES, but was "' . $collectMode . '"!',
                    1559627862);
        }
    }
}
