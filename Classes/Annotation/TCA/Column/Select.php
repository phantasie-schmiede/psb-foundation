<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Annotation\TCA\Column;

use PSB\PsbFoundation\Traits\PropertyInjection\ExtensionInformationServiceTrait;
use PSB\PsbFoundation\Utility\ValidationUtility;

/**
 * Class Select
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA\Column
 */
class Select extends AbstractColumnAnnotation
{
    use ExtensionInformationServiceTrait;

    public const EMPTY_DEFAULT_ITEM = [
        [
            'LLL:EXT:psb_foundation/Resources/Private/Language/Backend/Classes/Annotation/TCA/select.xlf:pleaseChoose',
            0,
        ],
    ];

    public const RENDER_TYPES = [
        'SELECT_SINGLE'                => self::RENDER_TYPE_SELECT_SINGLE,
        'SELECT_SINGLE_BOX'            => self::RENDER_TYPE_SELECT_SINGLE_BOX,
        'SELECT_CHECK_BOX'             => self::RENDER_TYPE_SELECT_CHECK_BOX,
        'SELECT_MULTIPLE_SIDE_BY_SIDE' => self::RENDER_TYPE_SELECT_MULTIPLE_SIDE_BY_SIDE,
        'SELECT_TREE'                  => self::RENDER_TYPE_SELECT_TREE,
    ];

    /*
     * Necessary for access in phpDoc-annotations.
     * @TODO: Can be removed when switching to php-attributes in php 8.
     */
    public const RENDER_TYPE_SELECT_CHECK_BOX             = 'selectCheckBox';
    public const RENDER_TYPE_SELECT_MULTIPLE_SIDE_BY_SIDE = 'selectMultipleSideBySide';
    public const RENDER_TYPE_SELECT_SINGLE                = 'selectSingle';
    public const RENDER_TYPE_SELECT_SINGLE_BOX            = 'selectSingleBox';
    public const RENDER_TYPE_SELECT_TREE                  = 'selectTree';

    public const TYPE = self::TYPES['SELECT'];

    /**
     * @var bool|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Select/Properties/AllowNonIdValues.html
     */
    protected ?bool $allowNonIdValues = null;

    /**
     * @var int|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/CommonProperties/AutoSizeMax.html
     */
    protected ?int $autoSizeMax = 1;

    /**
     * @var string|null
     */
    protected ?string $eval = null;

    /**
     * @var array|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/CommonProperties/FieldControl.html#tca-property-fieldcontrol
     */
    protected ?array $fieldControl = null;

    /**
     * @var bool|null
     */
    protected ?bool $fieldControlDisableAddRecord = null;

    /**
     * @var bool|null
     */
    protected ?bool $fieldControlDisableEditPopup = null;

    /**
     * @var bool|null
     */
    protected ?bool $fieldControlDisableListModule = null;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Select/Properties/ForeignTable.html
     */
    protected ?string $foreignTable = null;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Select/Properties/ForeignTableWhere.html
     */
    protected ?string $foreignTableWhere = null;

    /**
     * @var array|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Select/Properties/Items.html
     */
    protected ?array $items = null;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/11.5/en-us/ColumnsConfig/CommonProperties/ItemsProcFunc.html
     */
    protected ?string $itemsProcFunc = null;

    /**
     * Instead of directly specifying a foreign table, it is possible to specify a domain model class.
     *
     * @var string|null
     */
    protected ?string $linkedModel = null;

    /**
     * @var int
     */
    protected int $maxItems = 1;

    /**
     * @var int|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/CommonProperties/Minitems.html#tca-property-minitems
     */
    protected ?int $minItems = null;

    /**
     * @var bool|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/CommonProperties/Multiple.html#tca-property-multiple
     */
    protected ?bool $multiple = null;

    /**
     * @var array|null
     */
    protected ?array $prependItem = null;

    /**
     * @var string
     */
    protected string $renderType = self::RENDER_TYPES['SELECT_SINGLE'];

    /**
     * @var int|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/CommonProperties/Size.html#tca-property-size
     */
    protected ?int $size = 1;

    /**
     * @var array|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Select/Properties/SelectTreeConfig.html
     */
    protected ?array $treeConfig = null;

    /**
     * You can use the property name. It will be converted to the column name automatically.
     *
     * @var string|null
     */
    protected ?string $treeConfigChildrenField = null;

    /**
     * @var string|null
     */
    protected ?string $treeConfigDataProvider = null;

    /**
     * @var bool|null
     */
    protected ?bool $treeConfigExpandAll = null;

    /**
     * @var int|null
     */
    protected ?int $treeConfigMaxLevels = null;

    /**
     * @var string|null
     */
    protected ?string $treeConfigNonSelectableLevels = null;

    /**
     * You can use the property name. It will be converted to the column name automatically.
     *
     * @var string|null
     */
    protected ?string $treeConfigParentField = null;

    /**
     * @var bool|null
     */
    protected ?bool $treeConfigShowHeader = null;

    /**
     * @var string|null
     */
    protected ?string $treeConfigStartingPoints = null;

    /**
     * @return int|null
     */
    public function getAutoSizeMax(): ?int
    {
        return $this->autoSizeMax;
    }

    /**
     * @return string|null
     */
    public function getEval(): ?string
    {
        return $this->eval;
    }

    /**
     * @return array|null
     */
    public function getFieldControl(): ?array
    {
        $fieldControl = $this->fieldControl;

        if (true === $this->fieldControlDisableAddRecord) {
            $fieldControl['addRecord']['disabled'] = true;
        }

        if (true === $this->fieldControlDisableEditPopup) {
            $fieldControl['editPopup']['disabled'] = true;
        }

        if (true === $this->fieldControlDisableListModule) {
            $fieldControl['listModule']['disabled'] = true;
        }

        return $fieldControl;
    }

    /**
     * @return string|null
     */
    public function getForeignTable(): ?string
    {
        return $this->foreignTable;
    }

    /**
     * @return string|null
     */
    public function getForeignTableWhere(): ?string
    {
        return $this->foreignTableWhere;
    }

    /**
     * @return array|null
     */
    public function getItems(): ?array
    {
        if (null === $this->items && null === $this->prependItem) {
            return null;
        }

        $this->items = array_merge($this->prependItem ?? [], $this->items ?? []);
        $this->prependItem = [];

        return $this->items;
    }

    /**
     * @return string|null
     */
    public function getItemsProcFunc(): ?string
    {
        return $this->itemsProcFunc;
    }

    /**
     * @return int
     */
    public function getMaxItems(): int
    {
        return $this->maxItems;
    }

    /**
     * @return int|null
     */
    public function getMinItems(): ?int
    {
        return $this->minItems;
    }

    /**
     * @return string
     */
    public function getRenderType(): string
    {
        return $this->renderType;
    }

    /**
     * @return int|null
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * @return array|null
     */
    public function getTreeConfig(): ?array
    {
        if (self::RENDER_TYPES['SELECT_TREE'] !== $this->renderType) {
            return null;
        }

        $configuration = [
            'childrenField' => $this->tcaService->convertPropertyNameToColumnName($this->treeConfigChildrenField ?? ''),
            'parentField'   => $this->tcaService->convertPropertyNameToColumnName($this->treeConfigParentField ?? ''),
        ];

        if (null !== $this->treeConfigExpandAll) {
            $configuration['appearance']['expandAll'] = $this->treeConfigExpandAll;
        }

        if (0 < $this->treeConfigMaxLevels) {
            $configuration['appearance']['maxLevels'] = $this->treeConfigMaxLevels;
        }

        if (null !== $this->treeConfigNonSelectableLevels) {
            $configuration['appearance']['nonSelectableLevels'] = $this->treeConfigNonSelectableLevels;
        }

        if (null !== $this->treeConfigShowHeader) {
            $configuration['appearance']['showHeader'] = $this->treeConfigShowHeader;
        }

        if (null !== $this->treeConfigDataProvider) {
            $configuration['dataProvider'] = $this->treeConfigDataProvider;
        }

        if (null !== $this->treeConfigStartingPoints) {
            $configuration['startingPoints'] = $this->treeConfigStartingPoints;
        }

        return $configuration;
    }

    /**
     * @return bool|null
     */
    public function isAllowNonIdValues(): ?bool
    {
        return $this->allowNonIdValues;
    }

    /**
     * @return bool|null
     */
    public function isMultiple(): ?bool
    {
        return $this->multiple;
    }

    /**
     * @param bool $allowNonIdValues
     *
     * @return void
     */
    public function setAllowNonIdValues(bool $allowNonIdValues): void
    {
        $this->allowNonIdValues = $allowNonIdValues;
    }

    /**
     * @param int $autoSizeMax
     *
     * @return void
     */
    public function setAutoSizeMax(int $autoSizeMax): void
    {
        $this->autoSizeMax = $autoSizeMax;
    }

    /**
     * @param string|null $eval
     *
     * @return void
     */
    public function setEval(?string $eval): void
    {
        $this->eval = $eval;
    }

    /**
     * @param array $fieldControl
     *
     * @return void
     */
    public function setFieldControl(array $fieldControl): void
    {
        $this->fieldControl = $fieldControl;
    }

    /**
     * @param bool|null $fieldControlDisableAddRecord
     *
     * @return void
     */
    public function setFieldControlDisableAddRecord(?bool $fieldControlDisableAddRecord): void
    {
        $this->fieldControlDisableAddRecord = $fieldControlDisableAddRecord;
    }

    /**
     * @param bool|null $fieldControlDisableEditPopup
     *
     * @return void
     */
    public function setFieldControlDisableEditPopup(?bool $fieldControlDisableEditPopup): void
    {
        $this->fieldControlDisableEditPopup = $fieldControlDisableEditPopup;
    }

    /**
     * @param bool|null $fieldControlDisableListModule
     *
     * @return void
     */
    public function setFieldControlDisableListModule(?bool $fieldControlDisableListModule): void
    {
        $this->fieldControlDisableListModule = $fieldControlDisableListModule;
    }

    /**
     * @param string|null $foreignTable
     *
     * @return void
     */
    public function setForeignTable(?string $foreignTable): void
    {
        $this->foreignTable = $foreignTable;
    }

    /**
     * @param string|null $foreignTableWhere
     *
     * @return void
     */
    public function setForeignTableWhere(?string $foreignTableWhere): void
    {
        $this->foreignTableWhere = $foreignTableWhere;
    }

    /**
     * @param array $items
     *
     * @return void
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * @param string|null $itemsProcFunc
     */
    public function setItemsProcFunc(?string $itemsProcFunc): void
    {
        $this->itemsProcFunc = $itemsProcFunc;
    }

    /**
     * @param string $linkedModel
     *
     * @return void
     */
    public function setLinkedModel(string $linkedModel): void
    {
        $this->linkedModel = $linkedModel;

        if (class_exists($linkedModel)) {
            $this->setForeignTable($this->tcaService->convertClassNameToTableName($linkedModel));
        }
    }

    /**
     * @param int $maxItems
     *
     * @return void
     */
    public function setMaxItems(int $maxItems): void
    {
        $this->maxItems = $maxItems;
    }

    /**
     * @param int $minItems
     *
     * @return void
     */
    public function setMinItems(int $minItems): void
    {
        $this->minItems = $minItems;
    }

    /**
     * @param bool $multiple
     *
     * @return void
     */
    public function setMultiple(bool $multiple): void
    {
        $this->multiple = $multiple;
    }

    /**
     * @param array $prependItem
     *
     * @return void
     */
    public function setPrependItem(array $prependItem): void
    {
        $this->prependItem = $prependItem;
    }

    /**
     * @param string $renderType
     *
     * @return void
     */
    public function setRenderType(string $renderType): void
    {
        ValidationUtility::checkValueAgainstConstant(self::RENDER_TYPES, $renderType);

        if (self::RENDER_TYPES['SELECT_TREE'] === $renderType) {
            $this->autoSizeMax = null;
            $this->size = null;
        }

        $this->renderType = $renderType;
    }

    /**
     * @param int $size
     *
     * @return void
     */
    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    /**
     * @param array|null $treeConfig
     *
     * @return void
     */
    public function setTreeConfig(?array $treeConfig): void
    {
        $this->treeConfig = $treeConfig;
    }

    /**
     * @param string $treeConfigChildrenField
     *
     * @return void
     */
    public function setTreeConfigChildrenField(string $treeConfigChildrenField): void
    {
        $this->treeConfigChildrenField = $treeConfigChildrenField;
    }

    /**
     * @param string $treeConfigDataProvider
     *
     * @return void
     */
    public function setTreeConfigDataProvider(string $treeConfigDataProvider): void
    {
        $this->treeConfigDataProvider = $treeConfigDataProvider;
    }

    /**
     * @param bool|null $treeConfigExpandAll
     *
     * @return void
     */
    public function setTreeConfigExpandAll(?bool $treeConfigExpandAll): void
    {
        $this->treeConfigExpandAll = $treeConfigExpandAll;
    }

    /**
     * @param int $treeConfigMaxLevels
     *
     * @return void
     */
    public function setTreeConfigMaxLevels(int $treeConfigMaxLevels): void
    {
        $this->treeConfigMaxLevels = $treeConfigMaxLevels;
    }

    /**
     * @param string $treeConfigNonSelectableLevels
     *
     * @return void
     */
    public function setTreeConfigNonSelectableLevels(string $treeConfigNonSelectableLevels): void
    {
        $this->treeConfigNonSelectableLevels = $treeConfigNonSelectableLevels;
    }

    /**
     * @param string $treeConfigParentField
     *
     * @return void
     */
    public function setTreeConfigParentField(string $treeConfigParentField): void
    {
        $this->treeConfigParentField = $treeConfigParentField;
    }

    /**
     * @param bool|null $treeConfigShowHeader
     *
     * @return void
     */
    public function setTreeConfigShowHeader(?bool $treeConfigShowHeader): void
    {
        $this->treeConfigShowHeader = $treeConfigShowHeader;
    }

    /**
     * @param string $treeConfigStartingPoints
     *
     * @return void
     */
    public function setTreeConfigStartingPoints(string $treeConfigStartingPoints): void
    {
        $this->treeConfigStartingPoints = $treeConfigStartingPoints;
    }
}
