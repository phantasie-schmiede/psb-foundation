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
use PSB\PsbFoundation\Exceptions\MisconfiguredTcaException;
use PSB\PsbFoundation\Exceptions\UnsetPropertyException;
use PSB\PsbFoundation\Service\DocComment\DocCommentParserService;
use PSB\PsbFoundation\Service\DocComment\ValueParsers\TcaConfigParser;
use PSB\PsbFoundation\Service\DocComment\ValueParsers\TcaFieldConfigParser;
use PSB\PsbFoundation\Traits\InjectionTrait;
use PSB\PsbFoundation\Utility\ExtensionInformationUtility;
use PSB\PsbFoundation\Utility\LocalizationUtility;
use PSB\PsbFoundation\Utility\StringUtility;
use ReflectionClass;
use ReflectionException;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use function count;

/**
 * Class TcaService
 * @package PSB\PsbFoundation\Service\Configuration
 */
class TcaService
{
    use InjectionTrait;

    private const PROTECTED_COLUMNS = [
        'crdate',
        'pid',
        'tstamp',
        'uid',
    ];

    /**
     * @var string
     */
    private $className;

    /**
     * @var array
     */
    private $configuration;

    /**
     * @var string
     */
    private $defaultLabelPath;

    /**
     * @var array
     */
    private $preDefinedColumns;

    /**
     * @var string
     */
    private $table;

    /**
     * @param string      $classOrTableName
     * @param string|null $extensionKey
     *
     * @throws NoSuchCacheException
     * @throws ReflectionException
     */
    public function __construct(
        string $classOrTableName,
        string $extensionKey = null
    ) {
        $this->setDefaultLabelPath('LLL:EXT:' . ($extensionKey ?? ExtensionInformationUtility::convertClassNameToExtensionKey($classOrTableName)) . '/Resources/Private/Language/Backend/Configuration/TCA/');

        if (false !== mb_strpos($classOrTableName, '\\')) {
            $this->className = $classOrTableName;
            $this->table = ExtensionInformationUtility::convertClassNameToTableName($this->className);
            $this->configuration = $this->getDummyConfiguration($this->table);
            $this->setDefaultLabelPath($this->getDefaultLabelPath() . $this->table . '.xlf:');
            $this->setCtrlProperties([
                'title' => $this->getDefaultLabelPath() . 'domain.model',
            ]);
        } else {
            $this->table = $classOrTableName;
            $this->setDefaultLabelPath($this->getDefaultLabelPath() . 'Overrides/' . $this->table . '.xlf:');
            $this->configuration = $GLOBALS['TCA'][$this->table];
        }

        /**
         * remember the predefined columns (e.g. for versioning, translating) in order to exclude them when
         * auto-creating the showItemList
         */
        $this->setPreDefinedColumns(array_keys($this->configuration['columns']));
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
     * adds a property's configuration to the ['columns'] section of the TCA
     * also returns the field configuration, e.g. needed when adding columns to existing tables in TCA/Overrides
     *
     * Example:
     * $tempColumns = array_merge(
     *     $tcaService->addColumn(...),
     *     $tcaService->addColumn(...),
     *     $tcaService->addColumn(...)
     * );
     *
     * @param string $property                    name of the database column
     * @param string $type                        use constants of this class to see what is available and to avoid
     *                                            typos
     * @param array  $customFieldConfiguration    override array keys within the 'config'-part
     * @param array  $customPropertyConfiguration override array keys on the same level as 'config'
     * @param bool   $autoAddToDefaultType        whether field shall be appended to the 'showitem'-list of type 0
     *
     * @return array|null
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function addColumn(
        string $property,
        string $type,
        array $customFieldConfiguration = [],
        array $customPropertyConfiguration = [],
        bool $autoAddToDefaultType = true
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
            $fieldConfiguration = ExtensionManagementUtility::getFileFieldTCAConfig($property,
                [
                    'appearance' => [
                        'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:images.addFileReference',
                    ],
                    'maxitems'   => 9999,
                ], $allowedFileTypes);
        } else {
            $fieldConfiguration = Fields::getDefaultConfiguration($type);
        }

        ArrayUtility::mergeRecursiveWithOverrule($fieldConfiguration, $customFieldConfiguration);
        $propertyConfiguration = [
            'config'  => $fieldConfiguration,
            'exclude' => 0,
            'label'   => $this->getDefaultLabelPath() . $property,
        ];

        ArrayUtility::mergeRecursiveWithOverrule($propertyConfiguration, $customPropertyConfiguration);
        $this->configuration['columns'][$property] = $propertyConfiguration;

        if ($autoAddToDefaultType) {
            $this->addFieldToType($property);
        }

        return [$property => $propertyConfiguration];
    }

    /**
     * @param string $field
     * @param int    $index
     */
    public function addFieldToType(string $field, int $index = 0): void
    {
        $separator = '';

        if (isset($this->configuration['types'][$index]['showitem']) && '' !== $this->configuration['types'][$index]['showitem']) {
            $separator = ', ';
        }

        $this->configuration['types'][$index]['showitem'] .= $separator . $field;
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
     * @return $this
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws NoSuchCacheException
     * @throws ReflectionException
     * @throws UnsetPropertyException
     */
    public function buildFromDocComment(): self
    {
        if (null === $this->className) {
            throw new UnsetPropertyException('When instantiating you must provide a class name instead of ' . $this->table . ' if you want to use this feature!',
                1541351524);
        }

        $docCommentParserService = $this->get(DocCommentParserService::class);

        $reflection = GeneralUtility::makeInstance(ReflectionClass::class, $this->className);
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            $docComment = $docCommentParserService->parsePhpDocComment($this->className, $property->getName());

            if (isset($docComment[TcaFieldConfigParser::ANNOTATION_TYPE])) {
                $fieldConfig = $docComment[TcaFieldConfigParser::ANNOTATION_TYPE];
                $type = $fieldConfig['type'];
                unset($fieldConfig['type']);
                $config = $docComment[TcaConfigParser::ANNOTATION_TYPE] ?? [];
                $this->addColumn(ExtensionInformationUtility::convertPropertyNameToColumnName($property->getName(),
                    $this->className), $type, $fieldConfig, $config);
            }
        }

        $docComment = $docCommentParserService->parsePhpDocComment($this->className);

        if (isset($docComment[TcaConfigParser::ANNOTATION_TYPE])) {
            $this->setCtrlProperties($docComment[TcaConfigParser::ANNOTATION_TYPE]);
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
                    if (!in_array($column, $this->getPreDefinedColumns(), true)) {
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
