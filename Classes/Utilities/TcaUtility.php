<?php
declare(strict_types=1);

namespace PS\PsFoundation\Utilities;

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

use Exception;
use PS\PsFoundation\Services\DocComment\DocCommentParserService;
use PS\PsFoundation\Services\DocComment\ValueParsers\TcaConfigParser;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class TcaUtility
 * @package PS\PsFoundation\Utilities
 */
class TcaUtility
{
    public const FIELD_TYPES = [
        'CHECKBOX'    => 'checkbox',
        'DATE'        => 'date',
        'DATETIME'    => 'datetime',
        'DOCUMENT'    => 'document',
        'FILE'        => 'file',
        'FLOAT'       => 'float',
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

    private const FAL_PLACEHOLDER_TYPES = [
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
            //'foreign_selector' => 'foreign_selector' // for m:n-relations
            //'foreign_sortby' => 'foreignSortby',
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
            'MM'            => '',
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
            'eval'       => 'trim,required',
            'parameters' => [],
            'size'       => 50,
            'type'       => 'user',
            'userFunc'   => '',

        ],
    ];

    private const PROTECTED_COLUMNS = [
        'crdate',
        'pid',
        'tstamp',
        'uid',
    ];

    /**
     * @var string
     */
    protected $className;

    /**
     * @var array
     */
    private $configuration;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper
     */
    private $dataMapper;

    /**
     * @var string
     */
    protected $defaultLabelPath;

    /**
     * @var string
     */
    private $extensionKey;

    /**
     * @var array
     */
    private $preDefinedColumns;

    /**
     * @var string
     */
    private $table;

    /**
     * @param string $classOrTableName
     * @param string $extensionKey
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper $dataMapper
     */
    public function __construct(
        string $classOrTableName,
        string $extensionKey,
        DataMapper $dataMapper
    ) {
        $this->dataMapper = $dataMapper;
        $this->extensionKey = $extensionKey;
        $this->defaultLabelPath = 'LLL:EXT:'.$this->extensionKey.'/Resources/Private/Language/Backend/Configuration/TCA/';

        if (false !== strpos($classOrTableName, '\\')) {
            $this->className = $classOrTableName;
            $this->table = $this->dataMapper->convertClassNameToTableName($classOrTableName);
            $this->configuration = $this->getDummyConfiguration($this->table);
            $this->defaultLabelPath .= $this->table.'.xlf:';
            $this->setCtrlProperties([
                'title' => $this->defaultLabelPath.'tca.title',
            ]);
        } else {
            $this->table = $classOrTableName;
            $this->defaultLabelPath .= 'Overrides/'.$this->table.'.xlf:';
            $this->configuration = $GLOBALS['TCA'][$this->table];
        }

        /**
         * remember the predefined columns (e.g. for versioning, translating) in order to exclude them when
         * auto-creating the showItemList
         */
        $this->setPreDefinedColumns(array_keys($this->configuration['columns']));
    }

    /**
     * 'main' function
     *
     * Use the return of this function as return in your TCA-file
     *
     * @param bool $autoCreateShowItemList
     *
     * @return array
     * @throws Exception
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

        $this->validateConfiguration();
        $this->registerNewTableInGlobalTca();

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
     * @param string $property
     *
     * @return mixed
     */
    public function getCtrlProperty(string $property)
    {
        return $this->configuration['ctrl'][$property];
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
     * adds a property's configuration to the ['columns'] section of the TCA
     * also returns the field configuration, e.g. needed when adding columns to existing tables in TCA/Overrides
     *
     * Example:
     * $tempColumns = array_merge(
     *     $tcaUtility->addColumn(...),
     *     $tcaUtility->addColumn(...),
     *     $tcaUtility->addColumn(...)
     * );
     *
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
            if (\in_array($type, self::FAL_PLACEHOLDER_TYPES, true)) {
                switch ($type) {
                    case self::FIELD_TYPES['DOCUMENT']:
                        $allowedFileTypes = 'pdf';
                        break;
                    case self::FIELD_TYPES['FILE']:
                        $allowedFileTypes = '*';
                        break;
                    case self::FIELD_TYPES['IMAGE']:
                        $allowedFileTypes = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
                        break;
                    default:
                        $allowedFileTypes = '';
                }

                $config = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                    $property,
                    [
                        'appearance' => [
                            'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:images.addFileReference',
                        ],
                        'maxitems'   => 9999,
                    ],
                    $allowedFileTypes
                );
            } else {
                $config = self::FIELD_CONFIGURATIONS[$type];
            }

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

            return [$property => $fieldConfiguration];
        }

        return null;
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

    public function buildFromDocComment(): void
    {
        if (null === $this->className) {
            // throw exception
        }

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $docCommentParserService = $objectManager->get(DocCommentParserService::class);

        /** @var \ReflectionClass $reflection */
        $reflection = GeneralUtility::makeInstance(\ReflectionClass::class, $this->className);
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            $docComment = $docCommentParserService->parsePhpDocComment($this->className, $property->getName());
            if (isset($docComment[TcaConfigParser::ANNOTATION_TYPE])) {
                // @todo REMOVE BEFORE DEPLOYMENT!!!
                \TYPO3\CMS\Core\Utility\DebugUtility::debug($docComment);
            }
        }
    }

    /**
     * set columns and their order for sorting in list view (BE only)
     *
     * function excepts simple arrays like [property1, property2, ...] (order is ascending by default)
     * and associative arrays like
     * [
     *     'porperty 1' => QueryInterface::ORDER_ASCENDING,
     *     'porperty 2' => QueryInterface::ORDER_DESCENDING,
     *     ...
     * ]
     *
     * @param array $columns
     */
    public function enableAutomaticSorting(array $columns): void
    {
        $this->configuration['ctrl']['sortby'] = null;
        $orderings = [];

        foreach ($columns as $key => $value) {
            if (is_numeric($key)) {
                $orderings[] = $value.' '.QueryInterface::ORDER_ASCENDING;
            } else {
                if (!$value) {
                    $value = QueryInterface::ORDER_ASCENDING;
                }

                $orderings[] = $key.' '.$value;
            }
        }
        $this->configuration['ctrl']['default_sortby'] = implode(', ', $orderings);
    }

    /**
     * enables custom sorting in list view (BE only)
     *
     * @param string $column
     */
    public function enableManualSorting(string $column = 'sorting'): void
    {
        $this->configuration['ctrl']['default_sortby'] = null;
        $this->configuration['ctrl']['sortby'] = $column;
    }

    /**
     * @param string $table
     *
     * @return array
     */
    private function getDummyConfiguration(string $table): array
    {
        $ll = 'LLL:EXT:lang / locallang_general.xlf:LGL.';

        return [
            'ctrl'      => [
                'adminOnly'                => false,
                //'copyAfterDuplFields' => 'colPos, sys_language_uid',
                'crdate'                   => 'crdate',
                'cruser_id'                => 'cruser_id',
                'default_sortby'           => 'uid DESC',
                'delete'                   => 'deleted',
                'enablecolumns'            => [
                    'disabled'  => 'hidden',
                    'endtime'   => 'endtime',
                    'starttime' => 'starttime',
                ],
                //'groupName' => '',
                'hideAtCopy'               => false,
                'hideTable'                => false,
                'iconfile'                 => 'EXT:core / Resources /Public/Icons / T3Icons / mimetypes / mimetypes - x - sys_action.svg',
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
                    'label'   => $ll.'language',
                    'config'  => [
                        'type'                => 'select',
                        'renderType'          => 'selectSingle',
                        'foreign_table'       => 'sys_language',
                        'foreign_table_where' => 'ORDER BY sys_language.title',
                        'items'               => [
                            [$ll.'allLanguages', -1],
                            [$ll.'default_value', 0],
                        ],
                    ],
                ],
                'l10n_parent'      => [
                    'displayCond' => 'FIELD:sys_language_uid:>:0',
                    'exclude'     => 1,
                    'label'       => $ll.'l18n_parent',
                    'config'      => [
                        'type'                => 'select',
                        'renderType'          => 'selectSingle',
                        'items'               => [
                            ['', 0],
                        ],
                        'foreign_table'       => $table,
                        'foreign_table_where' => ' AND '.$table.'.pid =###CURRENT_PID### AND '.$table.'.sys_language_uid IN (-1,0)',
                    ],
                ],
                'l10n_diffsource'  => [
                    'config' => [
                        'type' => 'passthrough',
                    ],
                ],
                't3ver_label'      => [
                    'label'  => $ll.'versionLabel',
                    'config' => [
                        'type' => 'input',
                        'size' => 30,
                        'max'  => 255,
                    ],
                ],
                'hidden'           => [
                    'exclude' => 1,
                    'label'   => $ll.'hidden',
                    'config'  => [
                        'type' => 'check',
                    ],
                ],
                'starttime'        => [
                    'exclude' => 1,
                    'label'   => $ll.'starttime',
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
                    'label'   => $ll.'endtime',
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

    private function registerNewTableInGlobalTca(): void
    {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages($this->table);
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
            $this->table, 'EXT:'.$this->extensionKey.'/Resources/Private/Language/Backend/CSH/'.$this->table.'.xlf'
        );
    }

    /**
     * @throws Exception
     */
    private function validateConfiguration(): void
    {
        if (isset($this->configuration['ctrl']['sortby'])) {
            if (isset($this->configuration['ctrl']['default_sortby'])) {
                throw new Exception($this->table.': You have to decide whether to use sortby or default_sortby. Your current configuration defines both of them.',
                    1541107594);
            }

            if (\in_array($this->configuration['ctrl']['sortby'], self::PROTECTED_COLUMNS, true)) {
                throw new Exception($this->table.': Your current configuration would overwrite a reserved system column with sorting values!',
                    1541107601);
            }
        }
    }
}
