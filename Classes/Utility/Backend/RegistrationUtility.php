<?php
/** @noinspection UnsupportedStringOffsetOperationsInspection */
declare(strict_types=1);
namespace PSB\PsbFoundation\Utility\Backend;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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
use PSB\PsbFoundation\Service\DocComment\DocCommentParserService;
use PSB\PsbFoundation\Service\DocComment\ValueParsers\PluginActionParser;
use PSB\PsbFoundation\Service\DocComment\ValueParsers\PluginConfigParser;
use PSB\PsbFoundation\Traits\StaticInjectionTrait;
use PSB\PsbFoundation\Utility\ArrayUtility;
use PSB\PsbFoundation\Utility\StringUtility;
use PSB\PsbFoundation\Utility\TypoScript\TypoScriptUtility;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ArrayUtility as Typo3CoreArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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
     * @var string[]
     */
    private static $contentElementWizardGroups = [];

    /**
     * For use in ext_localconf.php files
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
        $ll = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TSconfig/Page/wizard.xlf:' . $group . '.elements.' . lcfirst($pluginName);
        $listType = str_replace('_', '', $extensionKey) . '_' . mb_strtolower($pluginName);
        $title = $ll . '.title';

        $configuration = [
            'description'          => $ll . '.description',
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
     * @throws NoSuchCacheException
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
                        self::COLLECT_MODES['CONFIGURE_PLUGINS']);

                    ExtensionUtility::configurePlugin(
                        $extensionInformation->getExtensionName(),
                        $pluginName,
                        $controllersAndCachedActions,
                        $controllersAndUncachedActions
                    );

                    self::addPluginToElementWizard($extensionInformation->getExtensionKey(),
                        $pluginConfiguration['group'] ?? mb_strtolower($extensionInformation->getVendorName()),
                        $pluginName, $pluginConfiguration['iconIdentifier'] ?? null);
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
     * @throws NoSuchCacheException
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

                    $iconIdentifier = $moduleConfiguration[PluginConfigParser::ANNOTATION_TYPE]['iconIdentifier'] ?? 'module-' . $submoduleKey;

                    ExtensionUtility::registerModule(
                        $extensionInformation->getExtensionName(),
                        $moduleConfiguration[PluginConfigParser::ANNOTATION_TYPE]['mainModuleName'] ?? 'web',
                        $submoduleKey,
                        $moduleConfiguration[PluginConfigParser::ANNOTATION_TYPE]['position'] ?? '',
                        $controllersAndActions,
                        [
                            'access'         => $moduleConfiguration[PluginConfigParser::ANNOTATION_TYPE]['access'] ?? 'group, user',
                            'icon'           => $moduleConfiguration[PluginConfigParser::ANNOTATION_TYPE]['icon'] ?? null,
                            'iconIdentifier' => GeneralUtility::makeInstance(IconRegistry::class)
                                ->isRegistered($iconIdentifier) ? $iconIdentifier : 'content-plugin',
                            'labels'         => $moduleConfiguration[PluginConfigParser::ANNOTATION_TYPE]['labels'] ?? 'LLL:EXT:' . $extensionInformation->getExtensionKey() . '/Resources/Private/Language/Backend/Modules/' . $submoduleKey . '.xlf',
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
     * @throws NoSuchCacheException
     * @throws ReflectionException
     */
    public static function registerPlugins(ExtensionInformationInterface $extensionInformation): void
    {
        if (is_iterable($extensionInformation->getPlugins())) {
            foreach ($extensionInformation->getPlugins() as $pluginName => $controllerClassNames) {
                if (is_iterable($controllerClassNames)) {
                    [$pluginConfiguration] = self::collectActionsAndConfiguration($controllerClassNames,
                        self::COLLECT_MODES['REGISTER_PLUGINS']);
                    ExtensionUtility::registerPlugin(
                        $extensionInformation->getExtensionName(),
                        $pluginName,
                        $pluginConfiguration['title'] ?? 'LLL:EXT:' . $extensionInformation->getExtensionKey() . '/Resources/Private/Language/Backend/Configuration/TCA/Overrides/tt_content.xlf:plugin.' . $pluginName . '.title'
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
        $header = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TSconfig/Page/wizard.xlf:' . $key . '.header';

        $pageTS['mod']['wizards']['newContentElement']['wizardItems'][$key] = [
            'header' => LocalizationUtility::translate($header) ? $header : ucfirst($key),
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
        // @TODO: find root page dynamically
        $pageTS = BackendUtility::getPagesTSconfig(1);

        if (!isset($pageTS['mod']['wizards']['newContentElement']['wizardItems'][$group])
            && !in_array($group, self::$contentElementWizardGroups, true)) {
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
     *
     * @return array
     * @throws ReflectionException
     * @throws NoSuchCacheException
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
                    if (!StringUtility::endsWith($methodName,
                            'Action') || StringUtility::startsWith($methodName,
                            'initialize') || in_array($method->getDeclaringClass()->getName(),
                            [AbstractModuleController::class, ActionController::class], true)) {
                        continue;
                    }

                    $docComment = $docCommentParserService->parsePhpDocComment($controllerClassName,
                        $methodName);

                    if (!isset($docComment[PluginActionParser::ANNOTATION_TYPE][PluginActionParser::FLAGS['IGNORE']])) {
                        $actionName = mb_substr($methodName, 0, -6);

                        if ($docComment[PluginActionParser::ANNOTATION_TYPE][PluginActionParser::FLAGS['DEFAULT']]) {
                            array_unshift($controllersAndCachedActions[$controllerClassName],
                                $actionName);
                        } else {
                            $controllersAndCachedActions[$controllerClassName][] = $actionName;
                        }

                        if (self::COLLECT_MODES['CONFIGURE_PLUGINS'] === $collectMode
                            && isset($docComment[PluginActionParser::ANNOTATION_TYPE][PluginActionParser::FLAGS['UNCACHED']])) {
                            $controllersAndUncachedActions[$controllerClassName][] = $actionName;
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
