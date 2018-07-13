<?php
declare(strict_types=1);

namespace PS\PsFoundation\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018 Daniel Ablass <dn@phantasie-schmiede.de>, Phantasie-Schmiede
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

use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * Class TcaUtility
 * @package PS\PsFoundation\Utility
 */
class TcaUtility
{
    public const FIELD_TYPES = [
        'CHECKBOX' => 'checkbox',
        'DATE'     => 'date',
        'DATETIME' => 'datetime',
        'FLOAT'    => 'float',
        'INLINE'   => 'inline',
        'INTEGER'  => 'integer',
        'LINK'     => 'link',
        'MM'       => 'mm',
        'SELECT'   => 'select',
        'STRING'   => 'string',
        'TEXT'     => 'text',
    ];

    private const FIELD_CONFIGURATIONS = [
        'checkbox' => [
            'type' => 'check',
        ],
        'date'     => [
            'dbType'     => 'date',
            'type'       => 'input',
            'renderType' => 'inputDateTime',
            'size'       => 7,
            'eval'       => 'date',
            'default'    => '0000-00-00',
        ],
        'datetime' => [
            'type'       => 'input',
            'renderType' => 'inputDateTime',
            'size'       => 12,
            'eval'       => 'datetime',
        ],
        'float'    => [
            'type' => 'input',
            'size' => 20,
            'eval' => 'double2',
        ],
        'inline'   => [
            'type'          => 'inline',
            'foreign_table' => '',
            'foreign_field' => '',
            //'foreign_sortby' => 'foreignSortby',
            //'foreign_selector' => 'foreign_selector' // for m:n-relations
            'maxitems'      => 9999,
            'appearance'    => [
                'collapseAll'                     => true,
                'expandSingle'                    => true,
                'levelLinksPosition'              => 'bottom',
                'useSortable'                     => true,
                'showPossibleLocalizationRecords' => true,
                'showRemovedLocalizationRecords'  => true,
                'showAllLocalizationLink'         => true,
                'showSynchronizationLink'         => true,
                'enabledControls'                 => [
                    'dragdrop' => true,
                ],
            ],
        ],
        'integer'  => [
            'type' => 'input',
            'size' => 20,
            'eval' => 'num',
        ],
        'link'     => [
            'type'       => 'input',
            'renderType' => 'inputLink',
            'size'       => 10,
        ],
        'mm'       => [
            'type'          => 'select',
            'renderType'    => 'selectMultipleSideBySide',
            'size'          => 10,
            'maxitems'      => 9999,
            'autoSizeMax'   => 30,
            'multiple'      => 0,
            'foreign_table' => '',
            'MM'            => '',
        ],
        'select'   => [
            'type'          => 'select',
            'renderType'    => 'selectSingle',
            'foreign_table' => '',
            'maxitems'      => 1,
        ],
        'string'   => [
            'type' => 'input',
            'size' => 20,
            'eval' => 'trim',
        ],
        'text'     => [
            'type'           => 'text',
            'enableRichtext' => true,
            'cols'           => 32,
            'rows'           => 5,
            'eval'           => 'trim',
        ],
    ];

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var array
     */
    protected $preDefinedColumns;

    /**
     * TcaUtility constructor.
     *
     * @param string $table
     * @param string $title
     * @param string $labelColumn
     */
    public function __construct(string $table, string $title = '', string $labelColumn = '')
    {
        $this->configuration = $this->getDummyConfiguration($table);
        $this->setPreDefinedColumns(array_keys($this->configuration['columns']));

        if ('' !== $title) {
            $this->setCtrlProperties([
                'title' => $title,
            ]);
        }

        if ('' !== $labelColumn) {
            $this->setCtrlProperties([
                'label' => $labelColumn,
            ]);
        }
    }

    /**
     * 'main' function
     * Use the return of this function as return in your TCA-file
     *
     * @param bool $autoCreateShowItemList
     *
     * @return array
     */
    public function getConfiguration(bool $autoCreateShowItemList = true): array
    {
        // configuration must have at least one type defined
        if ($autoCreateShowItemList) {
            if ('' === $this->configuration['types'][0]) {
                $columns = array_keys($this->configuration['columns']);
                foreach ($columns as $column) {
                    if (!\in_array($column, $this->getPreDefinedColumns(), true)) {
                        $this->addFieldToType($column);
                    }
                }
            }

            // add default access fields to all types
            $types = array_keys($this->configuration['types']);
            foreach ($types as $type) {
                $this->addFieldToType('--div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, hidden, starttime, endtime',
                    $type);
            }
        }

        return $this->configuration;
    }

    /**
     * @param array $configuration
     * @param bool $merge
     */
    public function setConfiguration(array $configuration, bool $merge = false): void
    {
        if ($merge) {
            ArrayUtility::mergeRecursiveWithOverrule($this->configuration, $configuration);
        } else {
            $this->configuration = $configuration;
        }
    }

    /**
     * @return array
     */
    public function getPreDefinedColumns(): array
    {
        return $this->preDefinedColumns;
    }

    /**
     * @param array $preDefinedColumns
     */
    public function setPreDefinedColumns(array $preDefinedColumns): void
    {
        $this->preDefinedColumns = $preDefinedColumns;
    }

    /**
     * @param array $ctrlProperties
     *
     * @return $this
     */
    public function setCtrlProperties(array $ctrlProperties): self
    {
        foreach ($ctrlProperties as $property => $value) {
            $this->configuration['ctrl'][$property] = $value;
        }

        return $this;
    }

    /**
     * @param string $property name of the database column
     * @param string $label BE label of the field (can begin with LLL:)
     * @param string $type use constants of this class to see what is available and to avoid typos
     * @param array $customFieldConfiguration override array keys within the 'config'-part
     * @param array $configuration override array keys on the same level as 'config'
     * @param bool $autoAddToDefaultType whether field shall be appended to the 'showitem'-list of type 0
     *
     * @return array|null
     */
    public function addColumn(
        string $property,
        string $label,
        string $type,
        array $customFieldConfiguration = [],
        array $configuration = [],
        bool $autoAddToDefaultType = true
    ): ?array {
        if (array_key_exists($type, self::FIELD_CONFIGURATIONS)) {
            $config = self::FIELD_CONFIGURATIONS[$type];
            ArrayUtility::mergeRecursiveWithOverrule($config, $customFieldConfiguration);
            $fieldConfiguration = [
                'exclude' => 0,
                'label'   => $label,
                'config'  => $config,
            ];

            ArrayUtility::mergeRecursiveWithOverrule($fieldConfiguration, $configuration);
            $this->configuration['columns'][$property] = $fieldConfiguration;

            if ($autoAddToDefaultType) {
                $this->addFieldToType($property);
            }

            /**
             * returns the pure field configuration
             * e.g. needed when adding columns to existing tables in TCA/Overrides
             */
            return $fieldConfiguration;
        }

        return null;
    }

    /**
     * @param string $fieldList
     * @param int|null $index
     */
    public function addType(string $fieldList, int $index = null): void
    {
        if (null === $index) {
            if (\count($this->configuration['types']) > 0) {
                $index = max(array_keys($this->configuration['types'])) + 1;
            } else {
                $index = 0;
            }
        }
        $this->configuration['types'][$index] = ['showitem' => $fieldList];
    }

    /**
     * @param string $field
     * @param int $index
     */
    public function addFieldToType(string $field, int $index = 0): void
    {
        $separator = '';

        if (isset($this->configuration['types'][$index]['showitem']) && '' !== $this->configuration['types'][$index]['showitem']) {
            $separator = ', ';
        }

        $this->configuration['types'][$index]['showitem'] .= $separator.$field;
    }

    /**
     * @param string $table
     *
     * @return array
     */
    protected function getDummyConfiguration(string $table): array
    {
        return [
            'ctrl'      => [
                'adminOnly'                => false,
                //'copyAfterDuplFields' => 'colPos, sys_language_uid',
                'crdate'                   => 'crdate',
                'cruser_id'                => 'cruser_id',
                'default_sortby'           => 'ORDER BY uid DESC',
                'delete'                   => 'deleted',
                'enablecolumns'            => [
                    'disabled'  => 'hidden',
                    'endtime'   => 'endtime',
                    'starttime' => 'starttime',
                ],
                //'groupName' => '',
                'hideAtCopy'               => false,
                'hideTable'                => false,
                'iconfile'                 => 'EXT:core/Resources/Public/Icons/T3Icons/mimetypes/mimetypes-x-sys_action.svg',
                'is_static'                => false,
                'label'                    => 'uid',
                'label_alt'                => '',
                'label_alt_force'          => false,
                'languageField'            => 'sys_language_uid',
                'origUid'                  => 't3_origuid',
                'prependAtCopy'            => '',
                'readOnly'                 => false,
                'rootLevel'                => 0,
                'searchFields'             => '',
                'setToDefaultOnCopy'       => '',
                //'sortby'                   => 'sorting',
                'thumbnail'                => '',
                'title'                    => 'My record',
                'translationSource'        => 'l10n_source',
                'transOrigDiffSourceField' => 'l10n_diffsource',
                'transOrigPointerField'    => 'l10n_parent',
                'tstamp'                   => 'tstamp',
                'type'                     => '',
                //'typeicon_classes' => '',
                //'icon_column' => '',
                //'useColumnsForDefaultValues' => '',
                'versioningWS'             => true,
            ],
            'interface' => [
                'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden',
            ],
            'types'     => [
                0 => ['showitem' => ''],
            ],
            'palettes'  => [
                //'0' => ['showitem' => ''],
            ],
            'columns'   => [
                'sys_language_uid' => [
                    'exclude' => 1,
                    'label'   => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
                    'config'  => [
                        'type'                => 'select',
                        'renderType'          => 'selectSingle',
                        'foreign_table'       => 'sys_language',
                        'foreign_table_where' => 'ORDER BY sys_language.title',
                        'items'               => [
                            ['LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1],
                            ['LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0],
                        ],
                    ],
                ],
                'l10n_parent'      => [
                    'displayCond' => 'FIELD:sys_language_uid:>:0',
                    'exclude'     => 1,
                    'label'       => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
                    'config'      => [
                        'type'                => 'select',
                        'renderType'          => 'selectSingle',
                        'items'               => [
                            ['', 0],
                        ],
                        'foreign_table'       => $table,
                        'foreign_table_where' => 'AND '.$table.'.pid=###CURRENT_PID### AND '.$table.'.sys_language_uid IN (-1,0)',
                    ],
                ],
                'l10n_diffsource'  => [
                    'config' => [
                        'type' => 'passthrough',
                    ],
                ],
                't3ver_label'      => [
                    'label'  => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
                    'config' => [
                        'type' => 'input',
                        'size' => 30,
                        'max'  => 255,
                    ],
                ],
                'hidden'           => [
                    'exclude' => 1,
                    'label'   => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
                    'config'  => [
                        'type' => 'check',
                    ],
                ],
                'starttime'        => [
                    'exclude' => 1,
                    'label'   => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
                    'config'  => [
                        'type'       => 'input',
                        'renderType' => 'inputDateTime',
                        'size'       => 13,
                        'eval'       => 'datetime',
                        'checkbox'   => 0,
                        'default'    => 0,
                        'range'      => [
                            'lower' => mktime(0, 0, 0, (int)date('m'), (int)date('d'), (int)date('Y')),
                        ],
                    ],
                ],
                'endtime'          => [
                    'exclude' => 1,
                    'label'   => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
                    'config'  => [
                        'type'       => 'input',
                        'renderType' => 'inputDateTime',
                        'size'       => 13,
                        'eval'       => 'datetime',
                        'checkbox'   => 0,
                        'default'    => 0,
                        'range'      => [
                            'lower' => mktime(0, 0, 0, (int)date('m'), (int)date('d'), (int)date('Y')),
                        ],
                    ],
                ],
            ],
        ];
    }
}
