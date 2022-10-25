<?php
/** @noinspection UnsupportedStringOffsetOperationsInspection */
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Service\Configuration;

use InvalidArgumentException;
use JsonException;
use PSB\PsbFoundation\Attribute\ModuleAction;
use PSB\PsbFoundation\Attribute\ModuleConfig;
use PSB\PsbFoundation\Attribute\PageType;
use PSB\PsbFoundation\Attribute\PluginAction;
use PSB\PsbFoundation\Attribute\PluginConfig;
use PSB\PsbFoundation\Controller\Backend\AbstractModuleController;
use PSB\PsbFoundation\Data\ExtensionInformationInterface;
use PSB\PsbFoundation\Service\ExtensionInformationService;
use PSB\PsbFoundation\Service\LocalizationService;
use PSB\PsbFoundation\Utility\ReflectionUtility;
use PSB\PsbFoundation\Utility\StringUtility;
use PSB\PsbFoundation\Utility\TypoScript\PageObjectConfiguration;
use PSB\PsbFoundation\Utility\TypoScript\TypoScriptUtility;
use PSB\PsbFoundation\Utility\ValidationUtility;
use ReflectionClass;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\DataHandling\PageDoktypeRegistry;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ArrayUtility as Typo3CoreArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use function in_array;
use function is_array;
use function is_int;

/**
 * Class RegistrationService
 *
 * @package PSB\PsbFoundation\Service\Configuration
 */
class RegistrationService
{
    public const ICON_SUFFIXES = [
        'CONTENT_FROM_PID' => '-contentFromPid',
        'ROOT'             => '-root',
        'HIDE_IN_MENU'     => '-hideinmenu',
    ];

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
     * typo3\sysext\backend\Configuration\TsConfig\Page\Mod\Wizards\NewContentElement.tsconfig
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
     * @param FlexFormService     $flexFormService
     * @param IconRegistry        $iconRegistry
     * @param PageDoktypeRegistry $pageDoktypeRegistry
     */
    public function __construct(
        protected FlexFormService $flexFormService,
        protected IconRegistry $iconRegistry,
        protected PageDoktypeRegistry $pageDoktypeRegistry,
    ) {
    }

    /**
     * For use in ext_localconf.php files
     *
     * @param ExtensionInformationInterface $extensionInformation
     * @param string                        $group
     * @param string                        $pluginName
     * @param string|null                   $iconIdentifier
     *
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     */
    public function addPluginToElementWizard(
        ExtensionInformationInterface $extensionInformation,
        string $group,
        string $pluginName,
        string $iconIdentifier = null,
    ): void {
        $iconIdentifier = $iconIdentifier ?? $extensionInformation->getExtensionKey() . '-' . str_replace('_', '-',
            GeneralUtility::camelCaseToLowerCaseUnderscored($pluginName));
        $ll = 'LLL:EXT:' . $extensionInformation->getExtensionKey() . '/Resources/Private/Language/Backend/Configuration/TsConfig/Page/Mod/Wizards/newContentElement.xlf:' . $group . '.elements.' . lcfirst($pluginName);
        $description = $ll . '.description';
        $title = $ll . '.title';
        $listType = str_replace('_', '', $extensionInformation->getExtensionKey()) . '_' . mb_strtolower($pluginName);
        $localizationService = GeneralUtility::makeInstance(LocalizationService::class);

        if (false === $localizationService->translationExists($description)) {
            $description = '';
        }

        if (false === $localizationService->translationExists($title, false)) {
            $title = $this->getDefaultLabelPathForPlugin($extensionInformation, $pluginName);

            if (false === $localizationService->translationExists($title)) {
                $title = $pluginName;
            }
        }

        $configuration = [
            'description'          => $description,
            'iconIdentifier'       => $this->iconRegistry->isRegistered($iconIdentifier) ? $iconIdentifier : 'content-plugin',
            'title'                => $title,
            'tt_content_defValues' => [
                'CType'     => 'list',
                'list_type' => $listType,
            ],
        ];

        $this->addElementWizardItem($configuration, $extensionInformation->getExtensionKey(), $group, $listType);
    }

    /**
     * For use in ext_localconf.php files
     *
     * @param ExtensionInformationInterface $extensionInformation
     *
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     */
    public function configurePlugins(ExtensionInformationInterface $extensionInformation): void
    {
        if (!is_iterable($extensionInformation->getPlugins())) {
            return;
        }

        foreach ($extensionInformation->getPlugins() as $pluginName => $controllerCollection) {
            if (!is_iterable($controllerCollection)) {
                // @TODO: Add warning?
                continue;
            }

            [
                $pluginConfiguration,
                $controllersAndCachedActions,
                $controllersAndUncachedActions,
            ] = $this->collectActionsAndConfiguration($controllerCollection,
                self::COLLECT_MODES['CONFIGURE_PLUGINS'], $pluginName);

            ExtensionUtility::configurePlugin(
                $extensionInformation->getExtensionName(),
                $pluginName,
                $controllersAndCachedActions,
                $controllersAndUncachedActions,
            );

            if (isset($pluginConfiguration[PluginConfig::class])) {
                /** @var PluginConfig $pluginConfig */
                $pluginConfig = $pluginConfiguration[PluginConfig::class];
                $group = $pluginConfig->getGroup();
                $iconIdentifier = $pluginConfig->getIconIdentifier();
            }

            $this->addPluginToElementWizard($extensionInformation,
                $group ?? mb_strtolower($extensionInformation->getVendorName()),
                $pluginName,
                $iconIdentifier ?? null);

            unset($group, $iconIdentifier, $pluginConfig);
        }
    }

    /**
     * For use in ext_tables.php files
     *
     * @param ExtensionInformationInterface $extensionInformation
     *
     * @return array
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     */
    public function buildModuleConfigurations(ExtensionInformationInterface $extensionInformation): array
    {
        $localizationService = GeneralUtility::makeInstance(LocalizationService::class);
        $modules = [];

        foreach ($extensionInformation->getMainModules() as $key => $value) {
            if (is_int($key)) {
                $mainModuleKey = $value;
            } else {
                $mainModuleKey = $key;
                $configuration = $value;
            }

            $configuration ??= [];
            $configuration['iconIdentifier'] ??= $this->getDefaultIconIdentifierForModule($extensionInformation,
                $mainModuleKey);
            $configuration['labels'] ??= $this->getDefaultLabelPathForModule($extensionInformation, $mainModuleKey);

            $this->checkLanguageLabelsForModule($configuration['labels'], $localizationService);
            $modules[$mainModuleKey] = $configuration;
            unset($configuration);
        }

        foreach ($extensionInformation->getModules() as $submoduleKey => $controllerClassNames) {
            if (is_iterable($controllerClassNames)) {
                [
                    $moduleConfiguration,
                    $controllersAndActions,
                ] = $this->collectActionsAndConfiguration($controllerClassNames,
                    self::COLLECT_MODES['REGISTER_MODULES']);

                if (!isset($moduleConfiguration[ModuleConfig::class])) {
                    continue;
                }

                /** @var ModuleConfig $moduleConfig */
                $moduleConfig = $moduleConfiguration[ModuleConfig::class];
                $iconIdentifier = $moduleConfig->getIconIdentifier() ?? $this->getDefaultIconIdentifierForModule($extensionInformation,
                    $submoduleKey);
                $mainModuleName = $moduleConfig->getMainModuleName();
                $position = $moduleConfig->getPosition();
                $access = $moduleConfig->getAccess();
                $labels = $moduleConfig->getLabels() ?? $this->getDefaultLabelPathForModule($extensionInformation,
                    $submoduleKey);
                $this->checkLanguageLabelsForModule($labels, $localizationService);
                $navigationComponentId = $moduleConfig->getNavigationComponentId();

                $modules[$submoduleKey] = [
                    'access'                => $access,
                    'controllerActions'     => $controllersAndActions,
                    'iconIdentifier'        => $this->iconRegistry->isRegistered($iconIdentifier) ? $iconIdentifier : 'content-plugin',
                    'labels'                => $labels,
                    'navigationComponentId' => $navigationComponentId,
                    'parent'                => $mainModuleName,
                    'position'              => $position,
                ];
            }
        }

        return $modules;
    }

    /**
     * @param ExtensionInformationInterface $extensionInformation
     * @param string                        $mode
     *
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
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
                $this->addPageTypeToGlobals($configuration, $doktype);
            } else {
                $this->addPageTypeToPagesTca($configuration, $doktype, $extensionInformation->getExtensionKey());
            }
        }
    }

    /**
     * For use in Configuration/TCA/Overrides/tt_content.php files
     *
     * @param ExtensionInformationInterface $extensionInformation
     *
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     */
    public function registerPlugins(ExtensionInformationInterface $extensionInformation): void
    {
        if (!is_iterable($extensionInformation->getPlugins())) {
            return;
        }

        $localizationService = GeneralUtility::makeInstance(LocalizationService::class);

        foreach ($extensionInformation->getPlugins() as $pluginName => $controllerCollection) {
            if (!is_iterable($controllerCollection)) {
                continue;
            }

            [$pluginConfiguration] = $this->collectActionsAndConfiguration($controllerCollection,
                self::COLLECT_MODES['REGISTER_PLUGINS']);

            $flexFormFilePath = 'EXT:' . $extensionInformation->getExtensionKey() . '/Configuration/FlexForms/' . $pluginName . '.xml';

            if (isset($pluginConfiguration[PluginConfig::class])) {
                /** @var PluginConfig $pluginConfig */
                $pluginConfig = $pluginConfiguration[PluginConfig::class];
                $title = $pluginConfig->getTitle();

                if ('' !== $pluginConfig->getFlexForm()) {
                    $flexFormFilePath = $pluginConfig->getFlexForm();

                    if (!str_contains($flexFormFilePath, 'EXT:')) {
                        $flexFormFilePath = 'EXT:' . $extensionInformation->getExtensionKey() . '/Configuration/FlexForms/' . $flexFormFilePath;
                    }
                }
            }

            $flexFormFilePath = GeneralUtility::getFileAbsFileName($flexFormFilePath);

            if (file_exists($flexFormFilePath)) {
                $pluginSignature = strtolower($extensionInformation->getExtensionName()) . '_' . strtolower($pluginName);
                $this->flexFormService->register(file_get_contents($flexFormFilePath), $pluginSignature);
            }

            if (empty($title)) {
                $title = $this->getDefaultLabelPathForPlugin($extensionInformation, $pluginName);

                if (false === $localizationService->translationExists($title)) {
                    $title = $pluginName;
                }
            }

            $iconIdentifier = $extensionInformation->getExtensionKey() . '-' . str_replace('_', '-',
                    GeneralUtility::camelCaseToLowerCaseUnderscored($pluginName));

            ExtensionUtility::registerPlugin(
                $extensionInformation->getExtensionName(),
                $pluginName,
                $title,
                $this->iconRegistry->isRegistered($iconIdentifier) ? $iconIdentifier : 'content-plugin',
            );

            unset ($title);
        }
    }

    /**
     * @param string $extensionKey
     * @param string $key
     *
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     */
    private function addElementWizardGroup(string $extensionKey, string $key): void
    {
        $header = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TsConfig/Page/Mod/Wizards/newContentElement.xlf:' . $key . '.header';
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
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     */
    private function addElementWizardItem(
        array $configuration,
        string $extensionKey,
        string $group,
        string $key,
    ): void {
        if (!in_array($group, $this->contentElementWizardGroups, true)) {
            $this->addElementWizardGroup($extensionKey, $group);
        }

        $newPageTS['mod']['wizards']['newContentElement']['wizardItems'][$group]['elements'][$key] = $configuration;
        ExtensionManagementUtility::addPageTSConfig(TypoScriptUtility::convertArrayToTypoScript($newPageTS));
    }

    /**
     * @param array $configuration
     * @param int   $doktype
     *
     * @return void
     */
    private function addPageTypeToGlobals(array $configuration, int $doktype): void
    {
        $configuration['allowedTables'] ??= '*';
        $configuration['type'] ??= 'web';

        if (is_array($configuration['allowedTables'])) {
            $configuration['allowedTables'] = implode(', ', $configuration['allowedTables']);
        }

        $this->pageDoktypeRegistry->add($doktype, $configuration);

        // Allow backend users to drag and drop the new page type:
        ExtensionManagementUtility::addUserTSConfig(
            'options.pageTree.doktypesToShowInNewPageDragArea := addToList(' . $doktype . ')',
        );
    }

    /**
     * @param array  $configuration
     * @param int    $doktype
     * @param string $extensionKey
     *
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     */
    private function addPageTypeToPagesTca(
        array $configuration,
        int $doktype,
        string $extensionKey,
    ): void {
        $table = 'pages';
        $label = $configuration['label'] ?? 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TCA/Overrides/page.xlf:pageType.' . $configuration['name'];

        if (false === GeneralUtility::makeInstance(LocalizationService::class)->translationExists($label)) {
            $label = ucfirst(str_replace('_', ' ',
                GeneralUtility::camelCaseToLowerCaseUnderscored($configuration['name'])));
        }

        // Add new page type as possible select item:
        ExtensionManagementUtility::addTcaSelectItem(
            $table,
            'doktype',
            [
                $label,
                $doktype,
            ],
            '1',
            'after',
        );

        $iconIdentifier = $configuration['iconIdentifier'] ?? 'page-type-' . str_replace('_', '-',
            GeneralUtility::camelCaseToLowerCaseUnderscored($configuration['name']));

        $icons = [
            $doktype => $iconIdentifier,
        ];

        foreach (self::ICON_SUFFIXES as $suffix) {
            if ($this->iconRegistry->isRegistered($iconIdentifier . $suffix)) {
                $icons[$doktype . $suffix] = $iconIdentifier . $suffix;
            }
        }

        Typo3CoreArrayUtility::mergeRecursiveWithOverrule(
            $GLOBALS['TCA'][$table],
            [
                // add icons for new page type:
                'ctrl'  => [
                    'typeicon_classes' => $icons,
                ],
                // add all page standard fields and tabs to your new page type
                'types' => [
                    $doktype => [
                        'showitem' => $GLOBALS['TCA'][$table]['types'][PageRepository::DOKTYPE_DEFAULT]['showitem'],
                    ],
                ],
            ],
        );
    }

    /**
     * @param string              $filePath
     * @param LocalizationService $localizationService
     *
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     */
    private function checkLanguageLabelsForModule(string $filePath, LocalizationService $localizationService): void
    {
        $localizationService->translationExists($filePath . ':mlang_labels_tabdescr');
        $localizationService->translationExists($filePath . ':mlang_labels_tablabel');
        $localizationService->translationExists($filePath . ':mlang_tabs_tab');
    }

    /**
     * @param array  $controllerCollection
     * @param string $collectMode
     * @param string $pluginName
     *
     * @return array
     */
    private function collectActionsAndConfiguration(
        array $controllerCollection,
        string $collectMode,
        string $pluginName = '',
    ): array {
        $configuration = [];
        $controllersAndCachedActions = [];
        $controllersAndUncachedActions = [];
        $extensionInformationService = GeneralUtility::makeInstance(ExtensionInformationService::class);

        foreach ($controllerCollection as $key => $value) {
            if (is_int($key)) {
                $controllerClassName = $value;
            } else {
                $controllerClassName = $key;
                $specifiedActions = $value;
            }

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

                    $actionName = mb_substr($methodName, 0, -6);

                    if (isset($specifiedActions) && !in_array($actionName, $specifiedActions, true)) {
                        continue;
                    }

                    if ($action = ReflectionUtility::getAttributeInstance(ModuleAction::class, $method)) {
                        /** @var ModuleAction $action */
                        if (true === $action->isDefault()) {
                            array_unshift($controllersAndCachedActions[$controllerClassName],
                                $actionName);
                        } else {
                            $controllersAndCachedActions[$controllerClassName][] = $actionName;
                        }
                    }

                    if ($action = ReflectionUtility::getAttributeInstance(PluginAction::class, $method)) {
                        /** @var PluginAction $action */
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

                        if ($pageType = ReflectionUtility::getAttributeInstance(PageType::class, $method)) {
                            /** @var PageType $pageType */
                            $extensionInformation = $extensionInformationService->extractExtensionInformationFromClassName($controllerClassName);
                            $pageObjectConfiguration = GeneralUtility::makeInstance(PageObjectConfiguration::class);
                            $pageObjectConfiguration->setAction($actionName);
                            $pageObjectConfiguration->setCacheable($pageType->isCacheable());
                            $pageObjectConfiguration->setContentType($pageType->getContentType());
                            $pageObjectConfiguration->setController($controllerClassName);
                            $pageObjectConfiguration->setDisableAllHeaderCode($pageType->isDisableAllHeaderCode());
                            $pageObjectConfiguration->setExtensionName($extensionInformation['extensionName']);
                            $pageObjectConfiguration->setPluginName($pluginName);
                            $pageObjectConfiguration->setTypeNum($pageType->getTypeNum());
                            $typoScriptObjectName = strtolower(implode('_', [
                                'ajax',
                                $extensionInformation['vendorName'],
                                $extensionInformation['extensionName'],
                                str_replace('\\', '', $pageObjectConfiguration->getController()),
                                $actionName,
                            ]));
                            $pageObjectConfiguration->setTypoScriptObjectName($typoScriptObjectName);
                            $pageObjectConfiguration->setVendorName($extensionInformation['vendorName']);
                            TypoScriptUtility::registerPageType($pageObjectConfiguration);
                        }
                    }
                }
            }

            Typo3CoreArrayUtility::mergeRecursiveWithOverrule($configuration,
                ReflectionUtility::getAttributeInstance(PluginConfig::class, $controller)?->toArray() ?? []);
            unset($specifiedActions);
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

        return match ($collectMode) {
            self::COLLECT_MODES['CONFIGURE_PLUGINS'] => [
                $configuration,
                $controllersAndCachedActions,
                $controllersAndUncachedActions,
            ],
            self::COLLECT_MODES['REGISTER_MODULES'] => [$configuration, $controllersAndCachedActions],
            self::COLLECT_MODES['REGISTER_PLUGINS'] => [$configuration],
            default => throw new InvalidArgumentException(__CLASS__ . ': $collectMode has to be a value defined in the constant COLLECT_MODES, but was "' . $collectMode . '"!',
                1559627862),
        };
    }

    /**
     * @param ExtensionInformationInterface $extensionInformation
     * @param string                        $moduleKey
     *
     * @return string
     */
    private function getDefaultIconIdentifierForModule(
        ExtensionInformationInterface $extensionInformation,
        string $moduleKey,
    ): string {
        return str_replace('_', '-', $extensionInformation->getExtensionKey()) . '-module-' . str_replace('_', '-',
                GeneralUtility::camelCaseToLowerCaseUnderscored($moduleKey));
    }

    /**
     * @param ExtensionInformationInterface $extensionInformation
     * @param string                        $moduleKey
     *
     * @return string
     */
    private function getDefaultLabelPathForModule(
        ExtensionInformationInterface $extensionInformation,
        string $moduleKey,
    ): string {
        return 'LLL:EXT:' . $extensionInformation->getExtensionKey() . '/Resources/Private/Language/Backend/Modules/' . lcfirst($moduleKey) . '.xlf';
    }

    /**
     * @param ExtensionInformationInterface $extensionInformation
     * @param string                        $pluginName
     *
     * @return string
     */
    private function getDefaultLabelPathForPlugin(
        ExtensionInformationInterface $extensionInformation,
        string $pluginName,
    ): string {
        return 'LLL:EXT:' . $extensionInformation->getExtensionKey() . '/Resources/Private/Language/Backend/Configuration/TCA/Overrides/tt_content.xlf:plugin.' . lcfirst($pluginName) . '.title';
    }
}
