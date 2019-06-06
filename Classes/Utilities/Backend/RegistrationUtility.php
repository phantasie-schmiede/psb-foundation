<?php
/** @noinspection UnsupportedStringOffsetOperationsInspection */
declare(strict_types=1);

namespace PSB\PsbFoundation\Utilities\Backend;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 PSG Web Team <webdev@plan.de>, PSG Plan Service Gesellschaft mbH
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
use PSB\PsbFoundation\Services\DocComment\DocCommentParserService;
use PSB\PsbFoundation\Services\DocComment\ValueParsers\PluginActionParser;
use PSB\PsbFoundation\Services\DocComment\ValueParsers\PluginConfigParser;
use PSB\PsbFoundation\Traits\StaticInjectionTrait;
use PSB\PsbFoundation\Utilities\ObjectUtility;
use PSB\PsbFoundation\Utilities\TypoScriptUtility;
use PSB\PsbFoundation\Utilities\VariableUtility;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class RegistrationUtility
 * @package PSB\PsbFoundation\Utilities\Backend
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
     * For use in ext_localconf.php
     *
     * @param string $extensionKey
     * @param string $group
     * @param string $pluginName
     * @param string $iconIdentifier
     */
    public static function addPluginToElementWizard(
        string $extensionKey,
        string $group,
        string $pluginName,
        string $iconIdentifier = null
    ): void {
        $iconIdentifier = $iconIdentifier ?? str_replace('_', '-',
                GeneralUtility::camelCaseToLowerCaseUnderscored($pluginName));
        $listType = str_replace('_', '', $extensionKey).'_'.strtolower($pluginName);
        $title = 'LLL:EXT:'.$extensionKey.'/Resources/Private/Language/Backend/Configuration/TSconfig/Page/wizard.xlf:'.$group.'.elements.'.lcfirst($pluginName).'.title';

        $configuration = [
            'description'          => 'LLL:EXT:'.$extensionKey.'/Resources/Private/Language/Backend/Configuration/TSconfig/Page/wizard.xlf:'.$group.'.elements.'.lcfirst($pluginName).'.description',
            'iconIdentifier'       => self::get(IconRegistry::class)
                ->isRegistered($iconIdentifier) ? $iconIdentifier : 'content-plugin',
            'title'                => LocalizationUtility::translate($title) ? $title : $pluginName,
            'tt_content_defValues' => [
                'CType'     => 'list',
                'list_type' => $listType,
            ],
        ];

        self::addElementWizardItem($configuration, $extensionKey, $group, $listType);
    }

    /**
     * For use in ext_localconf.php
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
        $configuration = [
            'description'          => 'LLL:EXT:'.$extensionKey.'/Resources/Private/Language/Backend/Configuration/TSconfig/Page/wizard.xlf:'.$group.'.elements.'.$contentType.'.description',
            'iconIdentifier'       => $iconIdentifier ?? $contentType,
            'title'                => 'LLL:EXT:'.$extensionKey.'/Resources/Private/Language/Backend/Configuration/TSconfig/Page/wizard.xlf:'.$group.'.elements.'.$contentType.'.title',
            'tt_content_defValues' => [
                'CType' => $internalContentType,
            ],
        ];

        self::addElementWizardItem($configuration, $extensionKey, $group, $internalContentType);

        $previewTemplate = GeneralUtility::getFileAbsFileName('EXT:'.$extensionKey.'/Resources/Private/Templates/Content/Preview/'.GeneralUtility::underscoredToUpperCamelCase($contentType).'.html');;

        if (is_file($previewTemplate)) {
            $pageTS['mod']['web_layout']['tt_content']['preview'][$internalContentType] = $previewTemplate;
            ExtensionManagementUtility::addPageTSConfig(TypoScriptUtility::convertArrayToTypoScript($pageTS));
        }

        $typoScript['tt_content'][$internalContentType] = [
            TypoScriptUtility::TYPO_SCRIPT_KEYS['OBJECT_TYPE'] => 'FLUIDTEMPLATE',
            'file'                                             => $templatePath ?? 'EXT:'.$extensionKey.'/Resources/Private/Templates/Content/'.GeneralUtility::underscoredToUpperCamelCase($contentType).'.html',
        ];

        ExtensionManagementUtility::addTypoScriptSetup(TypoScriptUtility::convertArrayToTypoScript($typoScript));
    }

    /**
     * For use in ext_localconf.php
     *
     * @param string $extensionInformation
     *
     * @throws ReflectionException
     */
    public static function configurePlugins(string $extensionInformation): void
    {
        self::validateExtensionInformation($extensionInformation);

        /** @var ExtensionInformationInterface $extensionInformation */
        if (is_iterable($extensionInformation::getPlugins())) {
            foreach ($extensionInformation::getPlugins() as $pluginName => $controllerClassNames) {
                if (is_iterable($controllerClassNames)) {
                    [
                        $pluginConfiguration,
                        $controllersAndCachedActions,
                        $controllersAndUncachedActions,
                    ] = self::collectActionsAndConfiguration($controllerClassNames,
                        self::COLLECT_MODES['CONFIGURE_PLUGINS']);

                    ExtensionUtility::configurePlugin(
                        $extensionInformation::getVendorName().'.'.$extensionInformation::getExtensionName(),
                        $pluginName,
                        $controllersAndCachedActions,
                        $controllersAndUncachedActions
                    );

                    self::addPluginToElementWizard($extensionInformation::getExtensionKey(),
                        $pluginConfiguration['group'] ?? strtolower($extensionInformation::getVendorName()),
                        $pluginName, $pluginConfiguration['iconIdentifier'] ?? null);
                }
            }
        }
    }

    /**
     * For use in Configuration/TCA/Overrides/tt_content.php
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
            throw new RuntimeException(__CLASS__.': TCA is not available yet!', 1553261710);
        }

        $internalKey = self::buildContentTypeKey($extensionKey, $key);

        ExtensionManagementUtility::addPlugin(
            [
                $title ?? 'LLL:EXT:'.$extensionKey.'/Resources/Private/Language/Backend/Configuration/TSconfig/Page/wizard.xlf:'.$group.'.elements.'.$key.'.title',
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
                array_search($target, ArrayUtility::flatten($contentTypeGroups), true));

            if ('after' === $operator) {
                $index++;
            }

            $contentTypeGroups[$group] = VariableUtility::insertIntoArray($contentTypeGroups[$group],
                [$newContentTypeConfiguration], $index);
        }

        $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] = [];

        foreach ($contentTypeGroups as $groupKey => $groupElements) {
            $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'][] = [
                $groupLabels[$groupKey] ?? 'LLL:EXT:'.$extensionKey.'/Resources/Private/Language/Backend/Configuration/TSconfig/Page/wizard.xlf:'.$groupKey.'.header',
                '--div--',
            ];

            array_push($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'], ...$groupElements);
        }

        $file = GeneralUtility::getFileAbsFileName('EXT:'.$extensionKey.'/Configuration/TCA/Content/'.$key.'.php');

        if (is_file($file)) {
            /** @noinspection PhpIncludeInspection */
            $tcaOfContentType = require $file;

            if (is_array($tcaOfContentType)) {
                $coreFields = [];
                $contentTypeFields = [];

                foreach ($tcaOfContentType as $field => $configuration) {
                    if (!isset($GLOBALS['TCA']['tt_content']['columns'][$field])) {
                        $contentTypeCondition = 'FIELD:CType:=:'.$internalKey;
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
                    '--div--;LLL:EXT:'.$extensionKey.'/Resources/Private/Language/Backend/Configuration/TSconfig/Page/wizard.xlf:'.$group.'.elements.'.$key.'.title',
                    implode(',', $contentTypeFields ?? []),
                ];

                $GLOBALS['TCA']['tt_content']['types'][$internalKey] = [
                    'showitem' => implode(',', $showItems),
                ];
            }
        }
    }

    /**
     * For use in ext_tables.php
     *
     * @param string $extensionInformation
     *
     * @throws ReflectionException
     */
    public static function registerModules(string $extensionInformation): void
    {
        self::validateExtensionInformation($extensionInformation);

        /** @var ExtensionInformationInterface $extensionInformation */
        if ('BE' === TYPO3_MODE && is_iterable($extensionInformation::getModules())) {
            foreach ($extensionInformation::getModules() as $submoduleKey => $controllerClassNames) {
                if (is_iterable($controllerClassNames)) {
                    [
                        $moduleConfiguration,
                        $controllersAndActions,
                    ] = self::collectActionsAndConfiguration($controllerClassNames,
                        self::COLLECT_MODES['REGISTER_MODULES']);

                    ExtensionUtility::registerModule(
                        $extensionInformation::getVendorName().'.'.$extensionInformation::getExtensionName(),
                        $moduleConfiguration[PluginConfigParser::ANNOTATION_TYPE]['mainModuleName'] ?? 'web',
                        $submoduleKey,
                        $moduleConfiguration[PluginConfigParser::ANNOTATION_TYPE]['position'] ?? '',
                        $controllersAndActions,
                        [
                            'access'         => $moduleConfiguration[PluginConfigParser::ANNOTATION_TYPE]['access'] ?? 'group, user',
                            'icon'           => $moduleConfiguration[PluginConfigParser::ANNOTATION_TYPE]['icon'] ?? null,
                            'iconIdentifier' => $moduleConfiguration[PluginConfigParser::ANNOTATION_TYPE]['iconIdentifier'] ?? 'module-'.$submoduleKey,
                            'labels'         => $moduleConfiguration[PluginConfigParser::ANNOTATION_TYPE]['labels'] ?? 'LLL:EXT:'.$extensionInformation::getExtensionKey().'/Resources/Private/Language/Backend/Modules/'.$submoduleKey.'.xlf',
                        ]
                    );
                }
            }
        }
    }

    /**
     * For use in Configuration/TCA/Overrides/tt_content.php
     *
     * @param string $extensionInformation
     *
     * @throws ReflectionException
     */
    public static function registerPlugins(string $extensionInformation): void
    {
        self::validateExtensionInformation($extensionInformation);

        /** @var ExtensionInformationInterface $extensionInformation */
        if (is_iterable($extensionInformation::getPlugins())) {
            foreach ($extensionInformation::getPlugins() as $pluginName => $controllerClassNames) {
                if (is_iterable($controllerClassNames)) {
                    [$pluginConfiguration] = self::collectActionsAndConfiguration($controllerClassNames,
                        self::COLLECT_MODES['REGISTER_PLUGINS']);

                    ExtensionUtility::registerPlugin(
                        $extensionInformation::getExtensionName(),
                        $pluginName,
                        $pluginConfiguration['title'] ?? 'LLL:EXT:'.$extensionInformation::getExtensionKey().'/Resources/Private/Language/Backend/Configuration/TCA/Overrides/tt_content.xlf:plugin.'.$pluginName.'.title'
                    );
                }
            }
        }
    }

    /**
     * @param string $className
     */
    public static function validateExtensionInformation(string $className): void
    {
        if (!class_exists($className) || !in_array(ExtensionInformationInterface::class, class_implements($className),
                true)) {
            throw ObjectUtility::get(InvalidArgumentException::class,
                __CLASS__.': "'.$className.'" is not the name of a class that implements the ExtensionInformationInterface!',
                1559676576, null);
        }
    }

    /**
     * @param string $extensionKey
     * @param string $key
     */
    private static function addElementWizardGroup(string $extensionKey, string $key): void
    {
        $header = 'LLL:EXT:'.$extensionKey.'/Resources/Private/Language/Backend/Configuration/TSconfig/Page/wizard.xlf:'.$key.'.header';

        $pageTS['mod']['wizards']['newContentElement']['wizardItems'][$key] = [
            'header' => LocalizationUtility::translate($header) ? $header : ucfirst($key),
            'show'   => '*',
        ];

        ExtensionManagementUtility::addPageTSConfig(TypoScriptUtility::convertArrayToTypoScript($pageTS));
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
        // @TODO: find root page dynamically
        $pageTS = BackendUtility::getPagesTSconfig(1);

        if (!isset($pageTS['mod']['wizards']['newContentElement']['wizardItems'][$group])) {
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
        return strtolower(str_replace('_', '', $extensionKey).'_'.$contentType);
    }

    /**
     * @param array  $controllerClassNames
     * @param string $collectMode
     *
     * @return array
     * @throws ReflectionException
     */
    private static function collectActionsAndConfiguration(array $controllerClassNames, string $collectMode): array
    {
        $configuration = [];
        $controllersAndCachedActions = [];
        $controllersAndUncachedActions = [];
        $docCommentParserService = self::get(DocCommentParserService::class);

        foreach ($controllerClassNames as $controllerClassName) {
            if (self::COLLECT_MODES['REGISTER_PLUGINS'] !== $collectMode) {
                $controller = self::get(ReflectionClass::class, $controllerClassName);
                $methods = $controller->getMethods();

                foreach ($methods as $method) {
                    $methodName = $method->getName();
                    if (!VariableUtility::endsWith($methodName,
                            'Action') || VariableUtility::startsWith($methodName,
                            'initialize') || in_array($method->getDeclaringClass()->getName(),
                            [AbstractModuleController::class, ActionController::class], true)) {
                        continue;
                    }

                    $docComment = $docCommentParserService->parsePhpDocComment($controllerClassName,
                        $methodName);

                    if (!isset($docComment[PluginActionParser::ANNOTATION_TYPE]['ignore'])) {
                        $actionName = substr($methodName, 0, -6);

                        if ($docComment[PluginActionParser::ANNOTATION_TYPE]['default']) {
                            array_unshift($controllersAndCachedActions[$controllerClassName::CONTROLLER_NAME],
                                $actionName);
                        } else {
                            $controllersAndCachedActions[$controllerClassName::CONTROLLER_NAME][] = $actionName;
                        }

                        if (self::COLLECT_MODES['CONFIGURE_PLUGINS'] === $collectMode && isset($docComment[PluginActionParser::ANNOTATION_TYPE]['uncached'])) {
                            $controllersAndUncachedActions[$controllerClassName::CONTROLLER_NAME][] = $actionName;
                        }
                    }
                }
            }

            if (self::COLLECT_MODES['CONFIGURE_PLUGINS'] !== $collectMode) {
                ArrayUtility::mergeRecursiveWithOverrule($configuration,
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
                    __CLASS__.': $collectMode has to be a value defined in the constant COLLECT_MODES, but was "'.$collectMode.'""!',
                    1559627862);
        }
    }
}
