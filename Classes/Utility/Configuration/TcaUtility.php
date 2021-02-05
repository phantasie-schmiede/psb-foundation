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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class TcaUtility
 *
 * @package PSB\PsbFoundation\Utility\Configuration
 */
class TcaUtility
{
    private const PROPERTY_KEY_MAPPING = [
        'autoSizeMax'        => 'autoSizeMax',
        'dbType'             => 'dbType',
        'defaultSortBy'      => 'default_sortby',
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
        'mmOppositeField'    => 'MM_opposite_field',
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
}
