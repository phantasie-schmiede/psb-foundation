<?php
/** @noinspection UnsupportedStringOffsetOperationsInspection */
declare(strict_types=1);

namespace PS\PsFoundation\Utilities\Backend;

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

use PS\PsFoundation\Utilities\VariableUtility;
use PS\PsFoundation\Services\GlobalVariableService;
use PS\PsFoundation\Utilities\TypoScriptUtility;
use RuntimeException;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

/**
 * Class ContentElementUtility
 * @package PS\PsFoundation\Utilities\Backend
 */
class ContentElementUtility
{
    /**
     * For use in ext_localconf.php
     *
     * @param string $extensionKey
     * @param string $group
     * @param string $pluginName
     * @param string $iconIdentifier
     *
     * @throws InvalidConfigurationTypeException
     */
    public static function addPluginToElementWizard(
        string $extensionKey,
        string $group,
        string $pluginName,
        string $iconIdentifier = null
    ): void {
        $listType = str_replace('_', '', $extensionKey).'_'.strtolower($pluginName);
        $configuration = [
            'description'          => 'LLL:EXT:'.$extensionKey.'/Resources/Private/Language/Backend/Configuration/TSconfig/Page/wizard.xlf:'.$group.'.elements.'.lcfirst($pluginName).'.description',
            'iconIdentifier'       => $iconIdentifier ?? str_replace('_', '-',
                    GeneralUtility::camelCaseToLowerCaseUnderscored($pluginName)),
            'title'                => 'LLL:EXT:'.$extensionKey.'/Resources/Private/Language/Backend/Configuration/TSconfig/Page/wizard.xlf:'.$group.'.elements.'.lcfirst($pluginName).'.title',
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
     *
     * @throws InvalidConfigurationTypeException
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
            $tcaOfContentType = require($file);

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
     * @param string $extensionKey
     * @param string $key
     */
    private static function addElementWizardGroup(string $extensionKey, string $key): void
    {
        $pageTS['mod']['wizards']['newContentElement']['wizardItems'][$key] = [
            'header' => 'LLL:EXT:'.$extensionKey.'/Resources/Private/Language/Backend/Configuration/TSconfig/Page/wizard.xlf:'.$key.'.header',
            'show'   => '*',
        ];

        ExtensionManagementUtility::addPageTSConfig(TypoScriptUtility::convertArrayToTypoScript($pageTS));
    }

    /**
     * @param array  $configuration
     * @param string $extensionKey
     * @param string $group
     * @param string $key
     *
     * @throws InvalidConfigurationTypeException
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
}
