<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Utility\Configuration;

use PSB\PsbFoundation\Data\ExtensionInformationInterface;
use PSB\PsbFoundation\Utility\FileUtility;
use PSB\PsbFoundation\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use function in_array;

/**
 * Class TcaUtility
 *
 * @package PSB\PsbFoundation\Utility\Configuration
 */
class TcaUtility
{
    public const CORE_FIELD_LABELS = [
        'ENABLED'     => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.enabled',
        'END_TIME'    => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
        'L18N_PARENT' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
        'LANGUAGE'    => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
        'START_TIME'  => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
    ];

    public const CORE_TAB_LABELS = [
        'ACCESS'   => 'LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access',
        'LANGUAGE' => 'LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language',
    ];

    /*
     * This array constant compensates inconsistencies in TCA key naming. All keys that are not listed here will be
     * kept unchanged.
     */
    private const PROPERTY_KEY_MAPPING = [
        'defaultSortBy'      => 'default_sortby',
        'enableRichText'     => 'enableRichtext',
        'foreignField'       => 'foreign_field',
        'foreignMatchFields' => 'foreign_match_fields',
        'foreignSortBy'      => 'foreign_sortby',
        'foreignTable'       => 'foreign_table',
        'foreignTableWhere'  => 'foreign_table_where',
        'internalType'       => 'internal_type',
        'l10nDisplay'        => 'l10n_display',
        'l10nMode'           => 'l10n_mode',
        'maxItems'           => 'maxitems',
        'minItems'           => 'minitems',
        'mm'                 => 'MM',
        'mmHasUidField'      => 'MM_hasUidField',
        'mmInsertFields'     => 'MM_insert_fields',
        'mmMatchFields'      => 'MM_match_fields',
        'mmOppositeField'    => 'MM_opposite_field',
        'sortBy'             => 'sortby',
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
            'label'   => self::CORE_FIELD_LABELS['ENABLED'],
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
            'label'   => self::CORE_FIELD_LABELS['END_TIME'],
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
            'label'   => self::CORE_FIELD_LABELS['LANGUAGE'],
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
            'label'   => self::CORE_FIELD_LABELS['START_TIME'],
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
            'label'       => self::CORE_FIELD_LABELS['L18N_PARENT'],
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
     *
     * @return void
     */
    public static function registerNewTablesInGlobalTca(ExtensionInformationInterface $extensionInformation): void
    {
        /*
         * The "Check for Broken Extensions" function in the upgrade module doesn't load the TCA. This breaks the
         * following code and would raise an exception if we didn't exit here in this case.
         * @see TYPO3\CMS\Install\Controller\UpgradeController::extensionCompatTesterLoadExtTablesAction()
         */
        if (null === $GLOBALS['TCA']) {
            return;
        }

        $identifier = 'tx_' . mb_strtolower($extensionInformation->getExtensionName()) . '_domain_model_';
        $newTables = array_filter(array_keys($GLOBALS['TCA']), static function ($key) use ($identifier) {
            return StringUtility::beginsWith($key, $identifier);
        });

        foreach ($newTables as $tableName) {
            if (true === $GLOBALS['TCA'][$tableName]['ctrl']['allowTableOnStandardPages']) {
                ExtensionManagementUtility::allowTableOnStandardPages($tableName);
            }

            $cshFilePath = 'EXT:' . $extensionInformation->getExtensionKey() . '/Resources/Private/Language/Backend/CSH/' . $tableName . '.xlf';

            if (!in_array($cshFilePath, $GLOBALS['TCA_DESCR'][$tableName]['refs'] ?? [], true)
                && FileUtility::fileExists($cshFilePath)
            ) {
                ExtensionManagementUtility::addLLrefForTCAdescr($tableName, $cshFilePath);
            }
        }
    }
}
