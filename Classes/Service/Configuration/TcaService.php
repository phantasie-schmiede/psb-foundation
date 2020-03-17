<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Service\Configuration;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019-2020 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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
use PSB\PsbFoundation\Data\ExtensionInformationInterface;
use PSB\PsbFoundation\Exceptions\AnnotationException;
use PSB\PsbFoundation\Exceptions\MisconfiguredTcaException;
use PSB\PsbFoundation\Service\DocComment\Annotations\TcaConfig;
use PSB\PsbFoundation\Service\DocComment\Annotations\TcaFieldConfig;
use PSB\PsbFoundation\Service\DocComment\DocCommentParserService;
use PSB\PsbFoundation\Traits\InjectionTrait;
use PSB\PsbFoundation\Utility\ExtensionInformationUtility;
use PSB\PsbFoundation\Utility\StringUtility;
use ReflectionClass;
use ReflectionException;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception as ObjectException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException;
use function count;

/**
 * Class TcaService
 *
 * @package PSB\PsbFoundation\Service\Configuration
 */
class TcaService
{
    use InjectionTrait;

    // This array constant compensates inconsistencies in TCA key naming.
    // All keys that are not listed here will be transformed to lower_case_underscored.
    private const PROPERTY_KEY_MAPPING = [
        'autoSizeMax'     => 'autoSizeMax',
        'enableRichtext'  => 'enableRichtext',
        'fieldControl'    => 'fieldControl',
        'maxItems'        => 'maxitems',
        'mm'              => 'MM',
        'mmOppositeField' => 'MM_opposite_field',
        'renderType'      => 'renderType',
        'userFunc'        => 'userFunc',
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
    private string $className;

    /**
     * @var array
     */
    private array $configuration;

    /**
     * @var string
     */
    private string $defaultLabelPath;

    /**
     * @var bool
     */
    private bool $overrideMode = false;

    /**
     * @var array
     */
    private array $preDefinedColumns;

    /**
     * @var string
     */
    private string $table;

    /**
     * TcaService constructor.
     *
     * @param string $className
     *
     * @throws AnnotationException
     * @throws InvalidArgumentForHashGenerationException
     * @throws ObjectException
     * @throws ReflectionException
     */
    public function __construct(string $className)
    {
        $this->className = $className;
        $this->table = ExtensionInformationUtility::convertClassNameToTableName($this->className);
        $extensionKey = ExtensionInformationUtility::extractExtensionInformationFromClassName($className)['extensionKey'];
        $this->setDefaultLabelPath('LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TCA/');

        if (isset($GLOBALS['TCA'][$this->table])) {
            $this->overrideMode = true;
            $this->setDefaultLabelPath($this->getDefaultLabelPath() . 'Overrides/' . $this->table . '.xlf:');
            $this->configuration = $GLOBALS['TCA'][$this->table];
        } else {
            $this->configuration = $this->getDummyConfiguration($this->table);
            $this->setDefaultLabelPath($this->getDefaultLabelPath() . $this->table . '.xlf:');
            $this->setCtrlProperties([
                'title' => $this->getDefaultLabelPath() . 'domain.model',
            ]);
        }

        /**
         * Remember the predefined columns (e.g. for versioning, translating or when overriding an existing TCA entry)
         * in order to exclude them when auto-creating the showItemList.
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
        if (true === $this->overrideMode) {
            $columns = array_keys($this->configuration['columns']);

            foreach ($columns as $column) {
                // only add new columns
                if (!in_array($column, $this->getPreDefinedColumns(), true)) {
                    ExtensionManagementUtility::addToAllTCAtypes(
                        $this->table,
                        $column
                    );
                }
            }
        } else {
            // configuration must have at least one type defined
            if ($autoCreateShowItemList) {
                if ('' === $this->configuration['types'][0]) {
                    $columns = array_keys($this->configuration['columns']);

                    foreach ($columns as $column) {
                        if (!in_array($column, $this->getPreDefinedColumns(), true)) {
                            $this->addFieldToType($column);
                        }
                    }
                }

                // add default access fields to all types
                $types = array_keys($this->configuration['types']);

                foreach ($types as $type) {
                    $this->addFieldToType('--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access, hidden, starttime, endtime',
                        $type);
                }
            }
        }

        $this->validateConfiguration();

        return $this->configuration;
    }

    /**
     * @param array $configuration
     * @param bool  $merge
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
     * @return string
     */
    public function getDefaultLabelPath(): string
    {
        return $this->defaultLabelPath;
    }

    /**
     * @param string $defaultLabelPath
     */
    public function setDefaultLabelPath(string $defaultLabelPath): void
    {
        $this->defaultLabelPath = $defaultLabelPath;
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
     * For usage in ext_tables.php
     *
     * @param ExtensionInformationInterface $extensionInformation
     */
    public static function registerNewTablesInGlobalTca(ExtensionInformationInterface $extensionInformation): void
    {
        $identifier = 'tx_' . mb_strtolower($extensionInformation->getExtensionName()) . '_domain_model_';

        $newTables = array_filter(array_keys($GLOBALS['TCA']), static function ($key) use ($identifier) {
            return StringUtility::startsWith($key, $identifier);
        });

        foreach ($newTables as $table) {
            ExtensionManagementUtility::allowTableOnStandardPages($table);
            ExtensionManagementUtility::addLLrefForTCAdescr($table,
                'EXT:' . $extensionInformation->getExtensionKey() . '/Resources/Private/Language/Backend/CSH/' . $table . '.xlf');
        }
    }

    /**
     * @param string $field
     * @param int    $typeIndex
     */
    public function addFieldToType(string $field, int $typeIndex = 0): void
    {
        $separator = '';

        if (isset($this->configuration['types'][$typeIndex]['showitem'])
            && '' !== $this->configuration['types'][$typeIndex]['showitem']
        ) {
            $separator = ', ';
        }

        $this->configuration['types'][$typeIndex]['showitem'] .= $separator . $field;
    }

    /**
     * @param string   $fieldList
     * @param int|null $index
     */
    public function addType(string $fieldList, int $index = null): void
    {
        if (null === $index) {
            if (0 < count($this->configuration['types'])) {
                $index = max(array_keys($this->configuration['types'])) + 1;
            } else {
                $index = 0;
            }
        }
        $this->configuration['types'][$index] = ['showitem' => $fieldList];
    }

    /**
     * adds a property's configuration to the ['columns'] section of the TCA
     * also returns the field configuration, e.g. needed when adding columns to existing tables in TCA/Overrides
     *
     * Example:
     * $tempColumns = array_merge(
     *     $tcaService->buildColumnConfiguration(...),
     *     $tcaService->buildColumnConfiguration(...),
     *     $tcaService->buildColumnConfiguration(...)
     * );
     *
     * @param string $columnName                  name of the database column
     * @param string $type                        use constants of this class to see what is available and to avoid
     *                                            typos
     * @param array  $customFieldConfiguration    override array keys within the 'config'-part
     * @param array  $customPropertyConfiguration override array keys on the same level as 'config'
     *
     * @return array
     */
    public function buildColumnConfiguration(
        string $columnName,
        string $type,
        array $customFieldConfiguration = [],
        array $customPropertyConfiguration = []
    ): ?array {
        if (in_array($type, Fields::FAL_PLACEHOLDER_TYPES, true)) {
            switch ($type) {
                case Fields::FIELD_TYPES['DOCUMENT']:
                    $allowedFileTypes = 'pdf';
                    break;
                case Fields::FIELD_TYPES['FILE']:
                    $allowedFileTypes = '*';
                    break;
                case Fields::FIELD_TYPES['IMAGE']:
                    $allowedFileTypes = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
                    break;
                default:
                    $allowedFileTypes = '';
            }

            /** @noinspection TranslationMissingInspection */
            $fieldConfiguration = ExtensionManagementUtility::getFileFieldTCAConfig($columnName,
                [
                    'appearance' => [
                        'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:images.addFileReference',
                    ],
                    'maxitems'   => 9999,
                ], $allowedFileTypes);
        } else {
            $fieldConfiguration = Fields::getDefaultConfiguration($type);
        }

        ArrayUtility::mergeRecursiveWithOverrule($fieldConfiguration, $this->convertKeys($customFieldConfiguration));
        $propertyConfiguration = [
            'config'  => $fieldConfiguration,
            'exclude' => 0,
            'label'   => $this->getDefaultLabelPath() . $columnName,
        ];

        ArrayUtility::mergeRecursiveWithOverrule($propertyConfiguration,
            $this->convertKeys($customPropertyConfiguration));

        return $propertyConfiguration;
    }

    /**
     * @return $this
     * @throws AnnotationException
     * @throws InvalidArgumentForHashGenerationException
     * @throws ObjectException
     * @throws ReflectionException
     */
    public function buildFromDocComment(): self
    {
        $docCommentParserService = $this->get(DocCommentParserService::class);
        $docComment = $docCommentParserService->parsePhpDocComment($this->className);
        $editableInFrontend = false;

        if (isset($docComment[TcaConfig::class])) {
            /** @var TcaConfig $tcaConfig */
            $tcaConfig = $docComment[TcaConfig::class];

            if (true === $tcaConfig->isEditableInFrontend()) {
                $editableInFrontend = true;
            }

            $this->setCtrlProperties($this->convertKeys($tcaConfig->toArray()));
        }

        $reflection = GeneralUtility::makeInstance(ReflectionClass::class, $this->className);
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            $docComment = $docCommentParserService->parsePhpDocComment($this->className, $property->getName());

            if (isset($docComment[TcaFieldConfig::class])) {
                /** @var TcaFieldConfig $tcaFieldConfig */
                $tcaFieldConfig = $docComment[TcaFieldConfig::class];
                $type = $tcaFieldConfig->getType();
                $tcaFieldConfig->setType(null);
                $tcaConfig = $docComment[TcaConfig::class] ?? $this->get(TcaConfig::class, []);

                if (true === $editableInFrontend) {
                    $tcaConfig->setEditableInFrontend(true);
                }

                $columnName = ExtensionInformationUtility::convertPropertyNameToColumnName($property->getName(),
                    $this->className);

                if (!in_array($columnName, $this->getPreDefinedColumns(), true)) {
                    $this->configuration['columns'][$columnName] = $this->buildColumnConfiguration($columnName,
                        $type,
                        $tcaFieldConfig->toArray(), $tcaConfig->toArray());
                    $this->addFieldToType($columnName);
                } elseif (true === $editableInFrontend) {
                    // @TODO: use a constant for 'editableInFrontend'?
                    $GLOBALS['TCA'][$this->table]['columns'][$columnName]['editableInFrontend'] = true;
                }
            }
        }

        return $this;
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
                $orderings[] = $value . ' ' . QueryInterface::ORDER_ASCENDING;
            } else {
                if (!$value) {
                    $value = QueryInterface::ORDER_ASCENDING;
                }

                $orderings[] = $key . ' ' . $value;
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
     * @param array $configuration
     *
     * @return array
     */
    private function convertKeys(array $configuration): array
    {
        $convertedArray = [];

        foreach ($configuration as $key => $value) {
            $key = self::PROPERTY_KEY_MAPPING[$key] ?? GeneralUtility::camelCaseToLowerCaseUnderscored($key);
            $convertedArray[$key] = $value;
        }

        return $convertedArray;
    }

    /**
     * @param string $table
     *
     * @return array
     */
    private function getDummyConfiguration(string $table): array
    {
        /** @noinspection TranslationMissingInspection */
        $ll = 'LLL:EXT:lang/locallang_general.xlf:LGL.';

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
            'palettes'  => [],
            'columns'   => [
                'sys_language_uid' => [
                    'exclude' => 1,
                    'label'   => $ll . 'language',
                    'config'  => [
                        'type'                => 'select',
                        'renderType'          => 'selectSingle',
                        'foreign_table'       => 'sys_language',
                        'foreign_table_where' => 'ORDER BY sys_language.title',
                        'items'               => [
                            [$ll . 'allLanguages', -1],
                            [$ll . 'default_value', 0],
                        ],
                    ],
                ],
                'l10n_parent'      => [
                    'displayCond' => 'FIELD:sys_language_uid:>:0',
                    'exclude'     => 1,
                    'label'       => $ll . 'l18n_parent',
                    'config'      => [
                        'type'                => 'select',
                        'renderType'          => 'selectSingle',
                        'items'               => [
                            ['', 0],
                        ],
                        'foreign_table'       => $table,
                        'foreign_table_where' => ' AND ' . $table . '.pid =###CURRENT_PID### AND ' . $table . '.sys_language_uid IN (-1,0)',
                    ],
                ],
                'l10n_diffsource'  => [
                    'config' => [
                        'type' => 'passthrough',
                    ],
                ],
                't3ver_label'      => [
                    'label'  => $ll . 'versionLabel',
                    'config' => [
                        'type' => 'input',
                        'size' => 30,
                        'max'  => 255,
                    ],
                ],
                'hidden'           => [
                    'exclude' => 1,
                    'label'   => $ll . 'hidden',
                    'config'  => [
                        'type' => 'check',
                    ],
                ],
                'starttime'        => [
                    'exclude' => 1,
                    'label'   => $ll . 'starttime',
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
                    'label'   => $ll . 'endtime',
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

    /**
     * @throws Exception
     */
    private function validateConfiguration(): void
    {
        if (isset($this->configuration['ctrl']['sortby'])) {
            if (isset($this->configuration['ctrl']['default_sortby'])) {
                throw new MisconfiguredTcaException($this->table . ': You have to decide whether to use sortby or default_sortby. Your current configuration defines both of them.',
                    1541107594);
            }

            if (in_array($this->configuration['ctrl']['sortby'], self::PROTECTED_COLUMNS, true)) {
                throw new MisconfiguredTcaException($this->table . ': Your current configuration would overwrite a reserved system column with sorting values!',
                    1541107601);
            }
        }
    }
}
