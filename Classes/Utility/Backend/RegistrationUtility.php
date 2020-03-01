<?php
/** @noinspection UnsupportedStringOffsetOperationsInspection */
declare(strict_types=1);
namespace PSB\PsbFoundation\Utility\Backend;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019-2020 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use InvalidArgumentException;
use PSB\PsbFoundation\Controller\Backend\AbstractModuleController;
use PSB\PsbFoundation\Data\ExtensionInformationInterface;
use PSB\PsbFoundation\Exceptions\AnnotationException;
use PSB\PsbFoundation\Service\DocComment\Annotations\AjaxPageType;
use PSB\PsbFoundation\Service\DocComment\Annotations\ModuleConfig;
use PSB\PsbFoundation\Service\DocComment\Annotations\PluginAction;
use PSB\PsbFoundation\Service\DocComment\Annotations\PluginConfig;
use PSB\PsbFoundation\Service\DocComment\DocCommentParserService;
use PSB\PsbFoundation\Traits\StaticInjectionTrait;
use PSB\PsbFoundation\Utility\ArrayUtility;
use PSB\PsbFoundation\Utility\ExtensionInformationUtility;
use PSB\PsbFoundation\Utility\StringUtility;
use PSB\PsbFoundation\Utility\TypoScript\PageObjectConfiguration;
use PSB\PsbFoundation\Utility\TypoScript\TypoScriptUtility;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ArrayUtility as Typo3CoreArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

/**
 * Class RegistrationUtility
 * @package PSB\PsbFoundation\Utility\Backend
 */
class RegistrationUtility
{
    use StaticInjectionTrait;

    private const COLLECT_MODES = [
        'CONFIGURE_PLUGINS' => 'configurePlugins',
        'REGISTER_MODULES'  => 'registerModules',
        'REGISTER_PLUGINS'  => 'registerPlugins',
    ];

    /**
     * This static variable is used to keep track of already registered wizard groups and is pre-filled with TYPO3's
     * default groups as defined in
     * typo3\sysext\backend\Configuration\TSconfig\Page\Mod\Wizards\NewContentElement.tsconfig
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
     * @param string $extensionKey
     * @param string $group
     * @param string $pluginName
     * @param string $iconIdentifier
     *
     * @throws Exception
     */
    public static function addPluginToElementWizard(
        string $extensionKey,
        string $group,
        string $pluginName,
        string $iconIdentifier = null
    ): void {
        $iconIdentifier = $iconIdentifier ?? str_replace('_', '-',
                GeneralUtility::camelCaseToLowerCaseUnderscored($pluginName));
        $ll = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TSconfig/Page/wizard.xlf:' . $group . '.elements.' . lcfirst($pluginName);
        $listType = str_replace('_', '', $extensionKey) . '_' . mb_strtolower($pluginName);

        $configuration = [
            'description'          => $ll . '.description',
            'iconIdentifier'       => self::get(IconRegistry::class)
                ->isRegistered($iconIdentifier) ? $iconIdentifier : 'content-plugin',
            'title'                => $ll . '.title',
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
     */
    public static function configureContentType(
        string $contentType,
        string $extensionKey,
        string $group,
        string $iconIdentifier = null,
        string $templatePath = null
    ): void {
        $internalContentType = self::buildContentTypeKey($extensionKey, $contentType);
        $ll = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TSconfig/Page/wizard.xlf:' . $group . '.elements.' . $contentType;
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

        ExtensionManagementUtility::addPlugin(
            [
                $title ?? 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TSconfig/Page/wizard.xlf:' . $group . '.elements.' . $key . '.title',
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
            $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'][] = [
                $groupLabels[$groupKey] ?? 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TSconfig/Page/wizard.xlf:' . $groupKey . '.header',
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

                $showItems = [
                    '--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xml:palette.general;general',
                    implode(',', $coreFields ?? []),
                    '--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xml:tabs.access',
                    '--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xml:palette.visibility;visibility',
                    '--palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xml:palette.access;access',
                    '--div--;LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TSconfig/Page/wizard.xlf:' . $group . '.elements.' . $key . '.title',
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
     * @throws IllegalObjectTypeException
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
                            'labels'                => $labels ?? 'LLL:EXT:' . $extensionInformation->getExtensionKey() . '/Resources/Private/Language/Backend/Modules/' . $submoduleKey . '.xlf',
                            'navigationComponentId' => $navigationComponentId ?? null,
                        ]
                    );
                }
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
     * @throws IllegalObjectTypeException
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

                    ExtensionUtility::registerPlugin(
                        $extensionInformation->getExtensionName(),
                        $pluginName,
                        $title ?? 'LLL:EXT:' . $extensionInformation->getExtensionKey() . '/Resources/Private/Language/Backend/Configuration/TCA/Overrides/tt_content.xlf:plugin.' . $pluginName . '.title'
                    );
                }
            }
        }
    }

    /**
     * @param string $extensionKey
     * @param string $key
     */
    private static function addElementWizardGroup(string $extensionKey, string $key): void
    {
        $pageTS['mod']['wizards']['newContentElement']['wizardItems'][$key] = [
            'header' => 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TSconfig/Page/wizard.xlf:' . $key . '.header',
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
        $docCommentParserService = self::get(DocCommentParserService::class);

        foreach ($controllerClassNames as $controllerClassName) {
            $controllersAndCachedActions[$controllerClassName] = [];

            if (self::COLLECT_MODES['REGISTER_PLUGINS'] !== $collectMode) {
                $controller = self::get(ReflectionClass::class, $controllerClassName);
                $methods = $controller->getMethods();

                foreach ($methods as $method) {
                    $methodName = $method->getName();

                    if (!StringUtility::endsWith($methodName,
                            'Action') || StringUtility::startsWith($methodName,
                            'initialize') || in_array($method->getDeclaringClass()->getName(),
                            [AbstractModuleController::class, ActionController::class], true)) {
                        continue;
                    }

                    $docComment = $docCommentParserService->parsePhpDocComment($controllerClassName,
                        $methodName);

                    if (isset($docComment[PluginAction::class])) {
                        /** @var PluginAction $pluginAction */
                        $pluginAction = $docComment[PluginAction::class];

                        if (false === $pluginAction->isIgnore()) {
                            $actionName = mb_substr($methodName, 0, -6);

                            if (true === $pluginAction->isDefault()) {
                                array_unshift($controllersAndCachedActions[$controllerClassName],
                                    $actionName);
                            } else {
                                $controllersAndCachedActions[$controllerClassName][] = $actionName;
                            }

                            if (self::COLLECT_MODES['CONFIGURE_PLUGINS'] === $collectMode
                                && true === $pluginAction->isUncached()) {
                                $controllersAndUncachedActions[$controllerClassName][] = $actionName;
                            }

                            if (isset($docComment[AjaxPageType::class])) {
                                $extensionInformation = ExtensionInformationUtility::extractExtensionInformationFromClassName($controllerClassName);
                                /** @var AjaxPageType $ajaxPageType */
                                $ajaxPageType = $docComment[AjaxPageType::class];
                                $pageObjectConfiguration = self::get(PageObjectConfiguration::class);
                                $pageObjectConfiguration->setAction($actionName);
                                $pageObjectConfiguration->setCacheable($ajaxPageType->isCacheable());
                                $pageObjectConfiguration->setContentType($ajaxPageType->getContentType());
                                $controllerName = ExtensionInformationUtility::convertControllerClassToBaseName($controllerClassName);
                                $pageObjectConfiguration->setController($controllerName);
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
                throw self::get(InvalidArgumentException::class,
                    __CLASS__ . ': $collectMode has to be a value defined in the constant COLLECT_MODES, but was "' . $collectMode . '""!',
                    1559627862);
        }
    }
}
