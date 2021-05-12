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
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        'autoSizeMax'        => 'autoSizeMax',
        'dbType'             => 'dbType',
        'defaultSortBy'      => 'default_sortby',
        'displayCond'        => 'displayCond',
        'editableInFrontend' => 'editableInFrontend',
        'enableRichtext'     => 'enableRichtext',
        'fallbackCharacter'  => 'fallbackCharacter',
        'fieldControl'       => 'fieldControl',
        'foreignSortBy'      => 'foreign_sortby',
        'generatorOptions'   => 'generatorOptions',
        'hideTable'          => 'hideTable',
        'maxItems'           => 'maxitems',
        'mm'                 => 'MM',
        'mmHasUidField'      => 'MM_hasUidField',
        'mmMatchFields'      => 'MM_match_fields',
        'mmOppositeField'    => 'MM_opposite_field',
        'onChange'           => 'onChange',
        'readOnly'           => 'readOnly',
        'renderType'         => 'renderType',
        'rootLevel'          => 'rootLevel',
        'sortBy'             => 'sortby',
        'userFunc'           => 'userFunc',
    ];

    /**
     * @param string $key
     *
     * @return string
     */
    public static function convertKey(string $key): string
    {
        return self::PROPERTY_KEY_MAPPING[$key] ?? GeneralUtility::camelCaseToLowerCaseUnderscored($key);
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
