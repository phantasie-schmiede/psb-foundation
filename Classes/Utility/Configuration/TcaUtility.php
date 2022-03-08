<?php
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

namespace PSB\PsbFoundation\Utility\Configuration;

use PSB\PsbFoundation\Data\ExtensionInformationInterface;
use PSB\PsbFoundation\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class TcaUtility
 *
 * @package PSB\PsbFoundation\Utility\Configuration
 */
class TcaUtility
{
    /*
     * This array constant compensates inconsistencies in TCA key naming. All keys that are not listed here will be
     * transformed to lower_case_underscored.
     */
    private const PROPERTY_KEY_MAPPING = [
        'defaultSortBy'     => 'default_sortby',
        'enableRichText'    => 'enableRichtext',
        'foreignField'      => 'foreign_field',
        'foreignSortBy'     => 'foreign_sortby',
        'foreignTable'      => 'foreign_table',
        'foreignTableWhere' => 'foreign_table_where',
        'maxItems'          => 'maxitems',
        'minItems'          => 'minitems',
        'mm'                => 'MM',
        'mmHasUidField'     => 'MM_hasUidField',
        'mmInsertFields'    => 'MM_insert_fields',
        'mmMatchFields'     => 'MM_match_fields',
        'mmOppositeField'   => 'MM_opposite_field',
        'sortBy'            => 'sortby',
    ];

    /**
     * @param string $key
     *
     * @return string
     */
    public static function convertKey(string $key): string
    {
        return self::PROPERTY_KEY_MAPPING[$key] ?? $key;
    }

    /**
     * @return array
     */
    public static function getDefaultConfigurationForDisabledField(): array
    {
        return [
            'config'  => [
                'items'      => [
                    [
                        0                    => '',
                        'invertStateDisplay' => true,
                    ],
                ],
                'renderType' => 'checkboxToggle',
                'type'       => 'check',
            ],
            'exclude' => true,
            'label'   => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.enabled',
        ];
    }

    /**
     * @return array
     */
    public static function getDefaultConfigurationForEndTimeField(): array
    {
        return [
            'config'  => [
                'behaviour'  => [
                    'allowLanguageSynchronization' => true,
                ],
                'default'    => 0,
                'eval'       => 'datetime, int',
                'range'      => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038),
                ],
                'renderType' => 'inputDateTime',
                'type'       => 'input',
            ],
            'exclude' => true,
            'label'   => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
        ];
    }

    /**
     * @return array
     */
    public static function getDefaultConfigurationForLanguageField(): array
    {
        return [
            'config'  => [
                'type' => 'language',
            ],
            'exclude' => true,
            'label'   => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
        ];
    }

    /**
     * @return array
     */
    public static function getDefaultConfigurationForStartTimeField(): array
    {
        return [
            'config'  => [
                'behaviour'  => [
                    'allowLanguageSynchronization' => true,
                ],
                'default'    => 0,
                'eval'       => 'datetime, int',
                'renderType' => 'inputDateTime',
                'type'       => 'input',
            ],
            'exclude' => true,
            'label'   => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
        ];
    }

    /**
     * @return string[][]
     */
    public static function getDefaultConfigurationForTransOrigDiffSourceField(): array
    {
        return [
            'config' => [
                'default' => '',
                'type'    => 'passthrough',
            ],
        ];
    }

    /**
     * @param string $tableName
     *
     * @return array
     */
    public static function getDefaultConfigurationForTransOrigPointerField(string $tableName): array
    {
        return [
            'config'      => [
                'default'             => 0,
                'foreign_table'       => $tableName,
                'foreign_table_where' => 'AND {#' . $tableName . '}.{#pid}=###CURRENT_PID### AND {#' . $tableName . '}.{#sys_language_uid} IN (-1,0)',
                'items'               => [
                    [
                        '',
                        0,
                    ],
                ],
                'renderType'          => 'selectSingle',
                'type'                => 'select',
            ],
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label'       => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
        ];
    }

    /**
     * @return string[][]
     */
    public static function getDefaultConfigurationForTranslationSourceField(): array
    {
        return [
            'config' => [
                'type' => 'passthrough',
            ],
        ];
    }

    /**
     * For usage in ext_tables.php
     *
     * @param ExtensionInformationInterface $extensionInformation
     */
    public static function registerNewTablesInGlobalTca(ExtensionInformationInterface $extensionInformation): void
    {
        $identifier = 'tx_' . mb_strtolower($extensionInformation->getExtensionName()) . '_domain_model_';
        $newTables = array_filter(array_keys($GLOBALS['TCA']), static function ($key) use ($identifier) {
            return StringUtility::beginsWith($key, $identifier);
        });

        foreach ($newTables as $table) {
            ExtensionManagementUtility::allowTableOnStandardPages($table);
            ExtensionManagementUtility::addLLrefForTCAdescr($table,
                'EXT:' . $extensionInformation->getExtensionKey() . '/Resources/Private/Language/Backend/CSH/' . $table . '.xlf');
        }
    }
}
