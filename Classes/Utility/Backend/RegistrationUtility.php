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

namespace PSB\PsbFoundation\Utility\Backend;

use InvalidArgumentException;
use PSB\PsbFoundation\Controller\Backend\AbstractModuleController;
use PSB\PsbFoundation\Data\ExtensionInformationInterface;
use PSB\PsbFoundation\Exceptions\AnnotationException;
use PSB\PsbFoundation\Service\DocComment\Annotations\AjaxPageType;
use PSB\PsbFoundation\Service\DocComment\Annotations\ModuleAction;
use PSB\PsbFoundation\Service\DocComment\Annotations\ModuleConfig;
use PSB\PsbFoundation\Service\DocComment\Annotations\PluginAction;
use PSB\PsbFoundation\Service\DocComment\Annotations\PluginConfig;
use PSB\PsbFoundation\Service\DocComment\DocCommentParserService;
use PSB\PsbFoundation\Utility\ArrayUtility;
use PSB\PsbFoundation\Utility\ExtensionInformationUtility;
use PSB\PsbFoundation\Utility\LocalizationUtility;
use PSB\PsbFoundation\Utility\ObjectUtility;
use PSB\PsbFoundation\Utility\StringUtility;
use PSB\PsbFoundation\Utility\TypoScript\PageObjectConfiguration;
use PSB\PsbFoundation\Utility\TypoScript\TypoScriptUtility;
use PSB\PsbFoundation\Utility\ValidationUtility;
use ReflectionClass;
use ReflectionException;
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
use TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

/**
 * Class RegistrationUtility
 *
 * @package PSB\PsbFoundation\Utility\Backend
 */
class RegistrationUtility
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
    private static array $contentElementWizardGroups = [
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
     */
    public static function addPluginToElementWizard(
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

        if (false === LocalizationUtility::translationExists($description)) {
            $description = '';
        }

        if (false === LocalizationUtility::translationExists($title)) {
            $title = $pluginName;
        }

        $configuration = [
            'description'          => $description,
            'iconIdentifier'       => ObjectUtility::get(IconRegistry::class)
                ->isRegistered($iconIdentifier) ? $iconIdentifier : 'content-plugin',
            'title'                => $title,
            'tt_content_defValues' => [
                'CType'     => 'list',
                'list_type' => $listType,
            ],
        ];

        self::addElementWizardItem($configuration, $extensionKey, $group, $listType);
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
     */
    public static function configureContentType(
        string $contentType,
        string $extensionKey,
        string $group,
        string $iconIdentifier = null,
        string $templatePath = null
    ): void {
        $internalContentType = self::buildContentTypeKey($extensionKey, $contentType);
        $ll = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TSConfig/Page/wizard.xlf:' . $group . '.elements.' . $contentType;
        LocalizationUtility::translationExists($ll . '.description');
        LocalizationUtility::translationExists($ll . '.title');

        $configuration = [
            'description'          => $ll . '.description',
            'iconIdentifier'       => $iconIdentifier ?? $contentType,
            'title'                => $ll . '.title',
            'tt_content_defValues' => [
                'CType' => $internalContentType,
            ],
        ];

        self::addElementWizardItem($configuration, $extensionKey, $group, $internalContentType);

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
     * @throws AnnotationException
     * @throws Exception
     * @throws InvalidArgumentForHashGenerationException
     * @throws ReflectionException
     */
    public static function configurePlugins(ExtensionInformationInterface $extensionInformation): void
    {
        if (is_iterable($extensionInformation->getPlugins())) {
            foreach ($extensionInformation->getPlugins() as $pluginName => $controllerClassNames) {
                if (is_iterable($controllerClassNames)) {
                    [
                        $pluginConfiguration,
                        $controllersAndCachedActions,
                        $controllersAndUncachedActions,
                    ] = self::collectActionsAndConfiguration($controllerClassNames,
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

                    self::addPluginToElementWizard($extensionInformation->getExtensionKey(),
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
     */
    public static function registerContentType(
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

        $internalKey = self::buildContentTypeKey($extensionKey, $key);

        if (null === $title) {
            $title = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TSConfig/Page/wizard.xlf:' . $group . '.elements.' . $key . '.title';
            LocalizationUtility::translationExists($title);
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
                LocalizationUtility::translationExists($groupKey);
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
     * @throws AnnotationException
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidArgumentForHashGenerationException
     * @throws ReflectionException
     */
    public static function registerModules(ExtensionInformationInterface $extensionInformation): void
    {
        if ('BE' === TYPO3_MODE && is_iterable($extensionInformation->getModules())) {
            foreach ($extensionInformation->getModules() as $submoduleKey => $controllerClassNames) {
                if (is_iterable($controllerClassNames)) {
                    [
                        $moduleConfiguration,
                        $controllersAndActions,
                    ] = self::collectActionsAndConfiguration($controllerClassNames,
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

                    LocalizationUtility::translationExists($labels . ':mlang_labels_tabdescr');
                    LocalizationUtility::translationExists($labels . ':mlang_labels_tablabel');
                    LocalizationUtility::translationExists($labels . ':mlang_tabs_tab');

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
    }

    /**
     * @param ExtensionInformationInterface $extensionInformation
     * @param string                        $mode
     *
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function registerPageTypes(ExtensionInformationInterface $extensionInformation, string $mode): void
    {
        ValidationUtility::checkValueAgainstConstant(self::PAGE_TYPE_REGISTRATION_MODES, $mode);
        $pageTypes = $extensionInformation->getPageTypes();

        if (empty($pageTypes)) {
            return;
        }

        foreach ($pageTypes as $doktype => $configuration) {
            if (self::PAGE_TYPE_REGISTRATION_MODES['EXT_TABLES'] === $mode) {
                self::addPageTypeToGlobals($doktype, $configuration['allowedTables'] ?? ['*'],
                    $configuration['type'] ?? 'web');
            } else {
                self::addPageTypeToPagesTca($doktype, $extensionInformation->getExtensionKey(),
                    $configuration['iconIdentifier'] ?? ('pageType-' . $configuration['name']), $configuration['name']);
            }
        }
    }

    /**
     * For use in Configuration/TCA/Overrides/tt_content.php files
     *
     * @param ExtensionInformationInterface $extensionInformation
     *
     * @throws AnnotationException
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidArgumentForHashGenerationException
     * @throws ReflectionException
     */
    public static function registerPlugins(ExtensionInformationInterface $extensionInformation): void
    {
        if (is_iterable($extensionInformation->getPlugins())) {
            foreach ($extensionInformation->getPlugins() as $pluginName => $controllerClassNames) {
                if (is_iterable($controllerClassNames)) {
                    [$pluginConfiguration] = self::collectActionsAndConfiguration($controllerClassNames,
                        self::COLLECT_MODES['REGISTER_PLUGINS']);

                    if (isset($pluginConfiguration[PluginConfig::class])) {
                        /** @var PluginConfig $pluginConfig */
                        $pluginConfig = $pluginConfiguration[PluginConfig::class];
                        $title = $pluginConfig->getTitle();
                    }

                    if (!isset($title) || null === $title) {
                        $title = 'LLL:EXT:' . $extensionInformation->getExtensionKey() . '/Resources/Private/Language/Backend/Configuration/TCA/Overrides/tt_content.xlf:plugin.' . $pluginName . '.title';
                        LocalizationUtility::translationExists($title);
                    }

                    $iconIdentifier = $extensionInformation->getExtensionKey() . '-' . str_replace('_', '-',
                            GeneralUtility::camelCaseToLowerCaseUnderscored($pluginName));

                    ExtensionUtility::registerPlugin(
                        $extensionInformation->getExtensionName(),
                        $pluginName,
                        $title,
                        ObjectUtility::get(IconRegistry::class)
                            ->isRegistered($iconIdentifier) ? $iconIdentifier : 'content-plugin'
                    );
                }

                unset ($title);
            }
        }
    }

    /**
     * @param string $extensionKey
     * @param string $key
     *
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    private static function addElementWizardGroup(string $extensionKey, string $key): void
    {
        $header = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TSConfig/Page/wizard.xlf:' . $key . '.header';
        LocalizationUtility::translationExists($header);
        $pageTS['mod']['wizards']['newContentElement']['wizardItems'][$key] = [
            'header' => $header,
            'show'   => '*',
        ];

        ExtensionManagementUtility::addPageTSConfig(TypoScriptUtility::convertArrayToTypoScript($pageTS));
        self::$contentElementWizardGroups[] = $key;
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
     */
    private static function addElementWizardItem(
        array $configuration,
        string $extensionKey,
        string $group,
        string $key
    ): void {
        if (!in_array($group, self::$contentElementWizardGroups, true)) {
            self::addElementWizardGroup($extensionKey, $group);
        }

        $newPageTS['mod']['wizards']['newContentElement']['wizardItems'][$group]['elements'][$key] = $configuration;
        ExtensionManagementUtility::addPageTSConfig(TypoScriptUtility::convertArrayToTypoScript($newPageTS));
    }

    /**
     * @param int            $doktype
     * @param array|string[] $allowedTables
     * @param string         $type
     */
    private static function addPageTypeToGlobals(int $doktype, array $allowedTables = ['*'], string $type = 'web'): void
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
     */
    private static function addPageTypeToPagesTca(
        int $doktype,
        string $extensionKey,
        string $iconIdentifier,
        string $name
    ): void {
        $table = 'pages';
        $label = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TCA/Overrides/pages.xlf:pageType.' . $name;
        LocalizationUtility::translationExists($label);

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
    private static function buildContentTypeKey(string $extensionKey, string $contentType): string
    {
        return mb_strtolower(str_replace('_', '', $extensionKey) . '_' . $contentType);
    }

    /**
     * @param array  $controllerClassNames
     * @param string $collectMode
     * @param string $pluginName
     *
     * @return array
     * @throws AnnotationException
     * @throws Exception
     * @throws InvalidArgumentForHashGenerationException
     * @throws ReflectionException
     */
    private static function collectActionsAndConfiguration(
        array $controllerClassNames,
        string $collectMode,
        string $pluginName = ''
    ): array {
        $configuration = [];
        $controllersAndCachedActions = [];
        $controllersAndUncachedActions = [];
        $docCommentParserService = ObjectUtility::get(DocCommentParserService::class);

        foreach ($controllerClassNames as $controllerClassName) {
            $controllersAndCachedActions[$controllerClassName] = [];

            if (self::COLLECT_MODES['REGISTER_PLUGINS'] !== $collectMode) {
                $controller = ObjectUtility::get(ReflectionClass::class, $controllerClassName);
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

                    $docComment = $docCommentParserService->parsePhpDocComment($controllerClassName,
                        $methodName);

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
                            $extensionInformation = ExtensionInformationUtility::extractExtensionInformationFromClassName($controllerClassName);
                            /** @var AjaxPageType $ajaxPageType */
                            $ajaxPageType = $docComment[AjaxPageType::class];
                            $pageObjectConfiguration = ObjectUtility::get(PageObjectConfiguration::class);
                            $pageObjectConfiguration->setAction($actionName);
                            $pageObjectConfiguration->setCacheable($ajaxPageType->isCacheable());
                            $pageObjectConfiguration->setContentType($ajaxPageType->getContentType());
                            $controllerName = ExtensionInformationUtility::convertControllerClassToBaseName($controllerClassName);
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
                    $docCommentParserService->parsePhpDocComment($controllerClassName));
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
                break;
            case self::COLLECT_MODES['REGISTER_MODULES']:
                return [$configuration, $controllersAndCachedActions];
                break;
            case self::COLLECT_MODES['REGISTER_PLUGINS']:
                return [$configuration];
                break;
            default:
                throw ObjectUtility::get(InvalidArgumentException::class,
                    __CLASS__ . ': $collectMode has to be a value defined in the constant COLLECT_MODES, but was "' . $collectMode . '"!',
                    1559627862);
        }
    }
}
