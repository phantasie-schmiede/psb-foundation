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

namespace PSB\PsbFoundation\Service\Configuration;

use Doctrine\Common\Annotations\AnnotationReader;
use Exception;
use InvalidArgumentException;
use JsonException;
use PSB\PsbFoundation\Annotation\TCA\AbstractTcaFalFieldAnnotation;
use PSB\PsbFoundation\Annotation\TCA\Ctrl;
use PSB\PsbFoundation\Annotation\TCA\Select;
use PSB\PsbFoundation\Annotation\TCA\TcaAnnotationInterface;
use PSB\PsbFoundation\Data\ExtensionInformationInterface;
use PSB\PsbFoundation\Exceptions\MisconfiguredTcaException;
use PSB\PsbFoundation\Traits\PropertyInjection\ClassesConfigurationFactoryTrait;
use PSB\PsbFoundation\Traits\PropertyInjection\ConnectionPoolTrait;
use PSB\PsbFoundation\Traits\PropertyInjection\ExtensionInformationServiceTrait;
use PSB\PsbFoundation\Traits\PropertyInjection\LocalizationServiceTrait;
use PSB\PsbFoundation\Utility\StringUtility;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception as ObjectException;
use TYPO3\CMS\Extbase\Persistence\ClassesConfiguration;

/**
 * Class TcaService
 *
 * @package PSB\PsbFoundation\Service\Configuration
 */
class TcaService
{
    use ClassesConfigurationFactoryTrait, ConnectionPoolTrait, ExtensionInformationServiceTrait, LocalizationServiceTrait;

    public const UNSET_KEYWORD = 'UNSET';

    protected const PROTECTED_COLUMNS = [
        'crdate',
        'pid',
        'tstamp',
        'uid',
    ];

    /**
     * @var array
     */
    protected static array $classTableMapping = [];

    /**
     * @var ClassesConfiguration|null
     */
    protected ?ClassesConfiguration $classesConfiguration = null;

    /**
     * This function will be executed when the core builds the TCA, but as it does not return an array there will be no
     * entry for the required file. Instead this function expands the TCA on its own by scanning through the domain
     * models of all registered extensions (extensions which provide an ExtensionInformation class, see
     * \PSB\PsbFoundation\Data\AbstractExtensionInformation).
     * Transient domain models (those without a corresponding table in the database) will be skipped.
     *
     * @param bool $overrideMode If set to false, the configuration of all original domain models (not extending other
     *                           domain models) is added to the TCA.
     *                           If set to true, the configuration of all extending domain models is added to the TCA.
     *
     * @throws ReflectionException
     * @throws Exception
     */
    public function buildTca(bool $overrideMode): void
    {
        if (empty(self::$classTableMapping)) {
            $this->buildClassesTableMapping();
        }

        if ($overrideMode) {
            $key = 'tcaOverrides';
        } else {
            $key = 'tca';
        }

        if (isset(self::$classTableMapping[$key])) {
            foreach (self::$classTableMapping[$key] as $fullQualifiedClassName => $tableName) {
                $this->buildFromDocComment($fullQualifiedClassName, $tableName);
            }
        }
    }

    /**
     * TYPO3's DataMapper can't be used here as it would create an incomplete class information cache due to the early
     * stage in which this function gets called!
     *
     * @param string $className
     *
     * @return string
     */
    public function convertClassNameToTableName(string $className): string
    {
        $classesConfiguration = $this->getClassesConfiguration();

        if ($classesConfiguration->hasClass($className)) {
            return $classesConfiguration->getConfigurationFor($className)['tableName'];
        }

        $classNameParts = GeneralUtility::trimExplode('\\', $className, true);

        // overwrite vendor name with extension prefix
        $classNameParts[0] = 'tx';

        return strtolower(implode('_', $classNameParts));
    }

    /**
     * @param string      $propertyName
     * @param string|null $className
     *
     * @return string
     */
    public function convertPropertyNameToColumnName(string $propertyName, string $className = null): string
    {
        if (null !== $className) {
            $classesConfiguration = $this->getClassesConfiguration();

            if ($classesConfiguration->hasClass($className)) {
                $configuration = $classesConfiguration->getConfigurationFor($className);

                if (isset($configuration['properties'][$propertyName])) {
                    return $configuration['properties'][$propertyName]['fieldName'];
                }
            }
        }

        return GeneralUtility::camelCaseToLowerCaseUnderscored($propertyName);
    }

    /**
     * For usage in ext_tables.php
     *
     * @param ExtensionInformationInterface $extensionInformation
     */
    public function registerNewTablesInGlobalTca(ExtensionInformationInterface $extensionInformation): void
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

    protected function buildClassesTableMapping(): void
    {
        self::$classTableMapping = [];
        $allExtensionInformation = $this->extensionInformationService->getExtensionInformation();

        foreach ($allExtensionInformation as $extensionInformation) {
            try {
                $finder = Finder::create()
                    ->files()
                    ->in(ExtensionManagementUtility::extPath($extensionInformation->getExtensionKey()) . 'Classes/Domain/Model')
                    ->name('*.php');
            } catch (InvalidArgumentException $e) {
                // No such directory in this extension
                continue;
            }

            /** @var SplFileInfo $fileInfo */
            foreach ($finder as $fileInfo) {
                $classNameComponents = array_merge(
                    [
                        $extensionInformation->getVendorName(),
                        $extensionInformation->getExtensionName(),
                        'Domain\Model',
                    ],
                    explode('/', substr($fileInfo->getRelativePathname(), 0, -4))
                );

                $fullQualifiedClassName = implode('\\', $classNameComponents);
                $reflectionClass = GeneralUtility::makeInstance(ReflectionClass::class, $fullQualifiedClassName);

                if ($reflectionClass->isAbstract() || $reflectionClass->isInterface()) {
                    continue;
                }

                $tableName = $this->convertClassNameToTableName($fullQualifiedClassName);

                $tableExists = $this->connectionPool
                    ->getConnectionForTable($tableName)
                    ->getSchemaManager()
                    ->tablesExist([$tableName]);

                if (!$tableExists) {
                    // This class seems to be no persistent domain model and will be skipped as a corresponding table is missing.
                    continue;
                }

                if (StringUtility::beginsWith($tableName,
                    'tx_' . mb_strtolower($extensionInformation->getExtensionName()))) {
                    self::$classTableMapping['tca'][$fullQualifiedClassName] = $tableName;
                } else {
                    self::$classTableMapping['tcaOverrides'][$fullQualifiedClassName] = $tableName;
                }
            }
        }
    }

    /**
     * @param string $className
     * @param string $tableName
     *
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws JsonException
     * @throws MisconfiguredTcaException
     * @throws ObjectException
     * @throws ReflectionException
     */
    protected function buildFromDocComment(string $className, string $tableName): void
    {
        $annotationReader = new AnnotationReader();
        $reflection = GeneralUtility::makeInstance(ReflectionClass::class, $className);

        /** @var Ctrl|null $ctrl */
        $ctrl = $annotationReader->getClassAnnotation($reflection, Ctrl::class);
        $editableInFrontend = false;

        if (null !== $ctrl && true === $ctrl->isEditableInFrontend()) {
            $editableInFrontend = true;
        }

        $extensionKey = $this->extensionInformationService->extractExtensionInformationFromClassName($className)['extensionKey'];
        $defaultLabelPath = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TCA/';

        if (isset($GLOBALS['TCA'][$tableName])) {
            $defaultLabelPath .= 'Overrides/' . $tableName . '.xlf:';
        } else {
            $defaultLabelPath .= $tableName . '.xlf:';
        }

        $properties = $reflection->getProperties();
        $columnConfigurations = [];

        foreach ($properties as $property) {
            $docComment = $annotationReader->getPropertyAnnotations($property);

            foreach ($docComment as $annotation) {
                if ($annotation instanceof TcaAnnotationInterface) {
                    if (true === $editableInFrontend && false !== $annotation->isEditableInFrontend()) {
                        $annotation->setEditableInFrontend(true);
                    }

                    $columnName = $this->convertPropertyNameToColumnName($property->getName(), $className);

                    if ('' === $annotation->getLabel()) {
                        $label = $defaultLabelPath . $columnName;
                        $this->localizationService->translationExists($label);
                        $annotation->setLabel($label);
                    }

                    if ($annotation instanceof Select
                        && [] !== $annotation->getItems()
                        && ArrayUtility::isAssociative($annotation->getItems()
                        )) {
                        $annotation->setItems($this->processSelectItemsArray($annotation->getItems(),
                            $defaultLabelPath . $columnName . '.'));
                    }

                    $columnConfigurations[$columnName] = $annotation;
                }
            }
        }

        if ([] === $columnConfigurations) {
            // No annotated properties found in class. Do nothing.
            return;
        }

        if (!isset($GLOBALS['TCA'][$tableName])) {
            $GLOBALS['TCA'][$tableName] = $this->getDummyConfiguration($tableName);
            $title = $defaultLabelPath . 'domain.model';
            $this->localizationService->translationExists($title);
            $GLOBALS['TCA'][$tableName]['ctrl']['title'] = $title;
        }

        if (null !== $ctrl) {
            foreach ($ctrl->toArray() as $property => $value) {
                if (self::UNSET_KEYWORD === $value) {
                    unset($GLOBALS['TCA'][$tableName]['ctrl'][$property]);
                } else {
                    $GLOBALS['TCA'][$tableName]['ctrl'][$property] = $value;
                }
            }
        }

        foreach ($columnConfigurations as $columnName => $annotation) {
            if ($annotation instanceof AbstractTcaFalFieldAnnotation) {
                $columnConfiguration = $annotation->toArray($columnName);
            } else {
                $columnConfiguration = $annotation->toArray();
            }

            ExtensionManagementUtility::addTCAcolumns($tableName, [$columnName => $columnConfiguration]);
            ExtensionManagementUtility::addToAllTCAtypes($tableName, $columnName, '', $annotation->getPosition());
        }

        $this->validateConfiguration($tableName);
    }

    /**
     * @return ClassesConfiguration
     */
    protected function getClassesConfiguration(): ClassesConfiguration
    {
        if (null === $this->classesConfiguration) {
            $this->classesConfiguration = $this->classesConfigurationFactory->createClassesConfiguration();
        }

        return $this->classesConfiguration;
    }

    /**
     * @param string $table
     *
     * @return array
     */
    protected function getDummyConfiguration(string $table): array
    {
        /** @noinspection TranslationMissingInspection */
        $ll = 'LLL:EXT:lang/locallang_general.xlf:LGL.';

        return [
            'ctrl'     => [
                'adminOnly'                => false,
                //'copyAfterDuplFields' => 'colPos, sys_language_uid',
                'crdate'                   => 'crdate',
                'cruser_id'                => 'cruser_id',
                'delete'                   => 'deleted',
                'enablecolumns'            => [
                    'disabled'  => 'hidden',
                    'endtime'   => 'endtime',
                    'starttime' => 'starttime',
                ],
                //'groupName' => '',
                'hideAtCopy'               => false,
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
            'types'    => [
                0 => ['showitem' => ''],
            ],
            'palettes' => [],
            'columns'  => [
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
     * @param array  $items
     * @param string $labelPath
     *
     * @return array
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws JsonException
     * @throws ObjectException
     */
    protected function processSelectItemsArray(array $items, string $labelPath): array
    {
        $selectItems = [];

        foreach ($items as $key => $value) {
            $identifier = GeneralUtility::underscoredToLowerCamelCase($key);
            $label = $labelPath . $identifier;

            if (!$this->localizationService->translationExists($label, false)) {
                $label = ucfirst($identifier);
            }

            $selectItems[] = [$label, $value];
        }

        return $selectItems;
    }

    /**
     * @param string $tableName
     *
     * @throws MisconfiguredTcaException
     */
    protected function validateConfiguration(string $tableName): void
    {
        $configuration = $GLOBALS['TCA'][$tableName];

        if (isset($configuration['ctrl']['sortby'])) {
            if (isset($configuration['ctrl']['default_sortby'])) {
                throw new MisconfiguredTcaException($tableName . ': You have to decide whether to use sortby or default_sortby. Your current configuration defines both of them.',
                    1541107594);
            }

            if (in_array($configuration['ctrl']['sortby'], self::PROTECTED_COLUMNS, true)) {
                throw new MisconfiguredTcaException($tableName . ': Your current configuration would overwrite a reserved system column with sorting values!',
                    1541107601);
            }
        }
    }
}
