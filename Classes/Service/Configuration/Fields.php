<?php
declare(strict_types=1);

namespace PSB\PsbFoundation\Service\Configuration;

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
use function in_array;

/**
 * Class Fields
 *
 * This helper class holds default configurations for a variety of field types. Use it to register fields in FlexForms
 * or TCA.
 *
 * @package PSB\PsbFoundation\Service\Configuration
 * @see     \PSB\PsbFoundation\Service\Configuration\FlexFormService
 * @see     \PSB\PsbFoundation\Service\Configuration\TcaService
 */
class Fields
{
    public const FIELD_TYPES = [
        'CHECKBOX'    => 'checkbox',
        'DATE'        => 'date',
        'DATETIME'    => 'datetime',
        'DOCUMENT'    => 'document',
        'FILE'        => 'file',
        'FLOAT'       => 'float',
        'GROUP'       => 'group',
        'IMAGE'       => 'image',
        'INLINE'      => 'inline',
        'INTEGER'     => 'integer',
        'LINK'        => 'link',
        'MM'          => 'mm',
        'PASSTHROUGH' => 'passthrough',
        'SELECT'      => 'select',
        'STRING'      => 'string',
        'TEXT'        => 'text',
        'USER'        => 'user',
    ];

    public const FAL_PLACEHOLDER_TYPES = [
        'document',
        'file',
        'image',
    ];

    private const FIELD_CONFIGURATIONS = [
        'checkbox'    => [
            'default' => 0,
            'type'    => 'check',
        ],
        'date'        => [
            'dbType'     => 'date',
            'default'    => '0000-00-00',
            'eval'       => 'date',
            'renderType' => 'inputDateTime',
            'size'       => 7,
            'type'       => 'input',
        ],
        'datetime'    => [
            'eval'       => 'datetime',
            'renderType' => 'inputDateTime',
            'size'       => 12,
            'type'       => 'input',
        ],
        'document'    => [],
        'file'        => [],
        'float'       => [
            'eval' => 'double2',
            'size' => 20,
            'type' => 'input',
        ],
        'group'       => [
            'allowed'       => 'pages',
            'internal_type' => 'db',
            'maxitems'      => 1,
            'minitems'      => 0,
            'size'          => 3,
            'type'          => 'group',
        ],
        'image'       => [],
        'inline'      => [
            'appearance'    => [
                'collapseAll'                     => true,
                'enabledControls'                 => [
                    'dragdrop' => true,
                ],
                'expandSingle'                    => true,
                'levelLinksPosition'              => 'bottom',
                'showAllLocalizationLink'         => true,
                'showPossibleLocalizationRecords' => true,
                'showRemovedLocalizationRecords'  => true,
                'showSynchronizationLink'         => true,
                'useSortable'                     => true,
            ],
            'foreign_field' => '',
            'foreign_table' => '',
            'maxitems'      => 9999,
            'type'          => 'inline',
        ],
        'integer'     => [
            'eval' => 'num',
            'size' => 20,
            'type' => 'input',
        ],
        'link'        => [
            'renderType' => 'inputLink',
            'size'       => 20,
            'type'       => 'input',
        ],
        'mm'          => [
            'autoSizeMax'   => 30,
            'foreign_table' => '',
            'maxitems'      => 9999,
            'mm'            => '',
            'multiple'      => 0,
            'renderType'    => 'selectMultipleSideBySide',
            'size'          => 10,
            'type'          => 'select',
        ],
        'passthrough' => [
            'type' => 'passthrough',
        ],
        'select'      => [
            'foreign_table' => '',
            'items'         => [],
            'maxitems'      => 1,
            'renderType'    => 'selectSingle',
            'type'          => 'select',
        ],
        'string'      => [
            'eval' => 'trim',
            'size' => 20,
            'type' => 'input',
        ],
        'text'        => [
            'cols'           => 32,
            'enableRichtext' => true,
            'eval'           => 'trim',
            'rows'           => 5,
            'type'           => 'text',
        ],
        'user'        => [
            'eval'       => 'required,trim',
            'parameters' => [],
            'size'       => 50,
            'type'       => 'user',
            'userFunc'   => '',
        ],
    ];

    /**
     * @param string $type
     *
     * @return array
     */
    public static function getDefaultConfiguration(string $type): array
    {
        self::checkFieldType($type);

        return self::FIELD_CONFIGURATIONS[$type];
    }

    /**
     * @param string $type
     */
    public static function checkFieldType(string $type): void
    {
        if (!in_array($type, self::FIELD_TYPES, true)) {
            throw new InvalidArgumentException(self::class . ': Value for type must be one of those defined in constant FIELD_TYPES!',
                1547452924);
        }
    }
}
