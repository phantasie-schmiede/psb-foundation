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
     * @var bool
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Select/Properties/AllowNonIdValues.html
     */
    protected bool $allowNonIdValues = false;

    /**
     * @var int
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/CommonProperties/AutoSizeMax.html
     */
    protected int $autoSizeMax = 1;

    /**
     * @var int|string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Select/Properties/Default.html
     */
    protected $default;

    /**
     * @var string|null
     */
    protected ?string $eval = null;

    /**
     * @var array
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/CommonProperties/FieldControl.html#tca-property-fieldcontrol
     */
    protected array $fieldControl = [
        'addRecord'  => [
            'disabled' => false,
        ],
        'editPopup'  => [
            'disabled' => false,
        ],
        'listModule' => [
            'disabled' => false,
        ],
    ];

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
     * @var array
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Select/Properties/Items.html
     */
    protected array $items = [];

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
     * @var int
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/CommonProperties/Minitems.html#tca-property-minitems
     */
    protected int $minItems = 0;

    /**
     * @var bool
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/CommonProperties/Multiple.html#tca-property-multiple
     */
    protected bool $multiple = false;

    /**
     * @var array
     */
    protected array $prependItem = [];

    /**
     * @var string
     */
    protected string $renderType = self::RENDER_TYPES['SELECT_SINGLE'];

    /**
     * @var int
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/CommonProperties/Size.html#tca-property-size
     */
    protected int $size = 1;

    /**
     * @var array|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Select/Properties/SelectTreeConfig.html
     */
    protected ?array $treeConfig = null;

    /**
     * You can use the property name. It will be converted to the column name automatically.
     *
     * @var string
     */
    protected string $treeConfigChildrenField = '';

    /**
     * @var string
     */
    protected string $treeConfigDataProvider = '';

    /**
     * @var bool|null
     */
    protected ?bool $treeConfigExpandAll = null;

    /**
     * @var int
     */
    protected int $treeConfigMaxLevels = 0;

    /**
     * @var string
     */
    protected string $treeConfigNonSelectableLevels = '0';

    /**
     * You can use the property name. It will be converted to the column name automatically.
     *
     * @var string
     */
    protected string $treeConfigParentField = '';

    /**
     * @var bool|null
     */
    protected ?bool $treeConfigShowHeader = null;

    /**
     * @var string
     */
    protected string $treeConfigStartingPoints = '';

    /**
     * @param array $prependItem
     */
    public function setPrependItem(array $prependItem): void
    {
        $this->prependItem = $prependItem;
    }

    /**
     * @param string $treeConfigChildrenField
     */
    public function setTreeConfigChildrenField(string $treeConfigChildrenField): void
    {
        $this->treeConfigChildrenField = $treeConfigChildrenField;
    }

    /**
     * @param string $treeConfigDataProvider
     */
    public function setTreeConfigDataProvider(string $treeConfigDataProvider): void
    {
        $this->treeConfigDataProvider = $treeConfigDataProvider;
    }

    /**
     * @param bool|null $treeConfigExpandAll
     */
    public function setTreeConfigExpandAll(?bool $treeConfigExpandAll): void
    {
        $this->treeConfigExpandAll = $treeConfigExpandAll;
    }

    /**
     * @param int $treeConfigMaxLevels
     */
    public function setTreeConfigMaxLevels(int $treeConfigMaxLevels): void
    {
        $this->treeConfigMaxLevels = $treeConfigMaxLevels;
    }

    /**
     * @param string $treeConfigNonSelectableLevels
     */
    public function setTreeConfigNonSelectableLevels(string $treeConfigNonSelectableLevels): void
    {
        $this->treeConfigNonSelectableLevels = $treeConfigNonSelectableLevels;
    }

    /**
     * @param string $treeConfigParentField
     */
    public function setTreeConfigParentField(string $treeConfigParentField): void
    {
        $this->treeConfigParentField = $treeConfigParentField;
    }

    /**
     * @param bool|null $treeConfigShowHeader
     */
    public function setTreeConfigShowHeader(?bool $treeConfigShowHeader): void
    {
        $this->treeConfigShowHeader = $treeConfigShowHeader;
    }

    /**
     * @param string $treeConfigStartingPoints
     */
    public function setTreeConfigStartingPoints(string $treeConfigStartingPoints): void
    {
        $this->treeConfigStartingPoints = $treeConfigStartingPoints;
    }

    /**
     * @return int
     */
    public function getAutoSizeMax(): int
    {
        return $this->autoSizeMax;
    }

    /**
     * @param int $autoSizeMax
     */
    public function setAutoSizeMax(int $autoSizeMax): void
    {
        $this->autoSizeMax = $autoSizeMax;
    }

    /**
     * @return int|string|null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param int|string|null $default
     */
    public function setDefault($default): void
    {
        $this->default = $default;
    }

    /**
     * @return string|null
     */
    public function getEval(): ?string
    {
        return $this->eval;
    }

    /**
     * @param string|null $eval
     */
    public function setEval(?string $eval): void
    {
        $this->eval = $eval;
    }

    /**
     * @return array
     */
    public function getFieldControl(): array
    {
        return $this->fieldControl;
    }

    /**
     * @param array $fieldControl
     */
    public function setFieldControl(array $fieldControl): void
    {
        $this->fieldControl = $fieldControl;
    }

    /**
     * @return string|null
     */
    public function getForeignTable(): ?string
    {
        return $this->foreignTable;
    }

    /**
     * @param string|null $foreignTable
     */
    public function setForeignTable(?string $foreignTable): void
    {
        $this->foreignTable = $foreignTable;
    }

    /**
     * @return string|null
     */
    public function getForeignTableWhere(): ?string
    {
        return $this->foreignTableWhere;
    }

    /**
     * @param string|null $foreignTableWhere
     */
    public function setForeignTableWhere(?string $foreignTableWhere): void
    {
        $this->foreignTableWhere = $foreignTableWhere;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        $this->items = array_merge($this->prependItem, $this->items);
        $this->prependItem = [];

        return $this->items;
    }

    /**
     * @param array $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * @param string $linkedModel
     */
    public function setLinkedModel(string $linkedModel): void
    {
        $this->linkedModel = $linkedModel;

        if (class_exists($linkedModel)) {
            $this->setForeignTable($this->tcaService->convertClassNameToTableName($linkedModel));
        }
    }

    /**
     * @return int
     */
    public function getMaxItems(): int
    {
        return $this->maxItems;
    }

    /**
     * @param int $maxItems
     */
    public function setMaxItems(int $maxItems): void
    {
        $this->maxItems = $maxItems;
    }

    /**
     * @return int
     */
    public function getMinItems(): int
    {
        return $this->minItems;
    }

    /**
     * @param int $minItems
     */
    public function setMinItems(int $minItems): void
    {
        $this->minItems = $minItems;
    }

    /**
     * @return string
     */
    public function getRenderType(): string
    {
        return $this->renderType;
    }

    /**
     * @param string $renderType
     */
    public function setRenderType(string $renderType): void
    {
        ValidationUtility::checkValueAgainstConstant(self::RENDER_TYPES, $renderType);
        $this->renderType = $renderType;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize(int $size): void
    {
        $this->size = $size;
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
            'childrenField' => $this->tcaService->convertPropertyNameToColumnName($this->treeConfigChildrenField),
            'parentField'   => $this->tcaService->convertPropertyNameToColumnName($this->treeConfigParentField),
        ];

        if (null !== $this->treeConfigExpandAll) {
            $configuration['appearance']['expandAll'] = $this->treeConfigExpandAll;
        }

        if (0 < $this->treeConfigMaxLevels) {
            $configuration['appearance']['maxLevels'] = $this->treeConfigMaxLevels;
        }

        if ('0' !== $this->treeConfigNonSelectableLevels) {
            $configuration['appearance']['nonSelectableLevels'] = $this->treeConfigNonSelectableLevels;
        }

        if (null !== $this->treeConfigShowHeader) {
            $configuration['appearance']['showHeader'] = $this->treeConfigShowHeader;
        }

        if ('' !== $this->treeConfigDataProvider) {
            $configuration['dataProvider'] = $this->treeConfigDataProvider;
        }

        if ('' !== $this->treeConfigStartingPoints) {
            $configuration['startingPoints'] = $this->treeConfigStartingPoints;
        }

        return $configuration;
    }

    /**
     * @return bool
     */
    public function isAllowNonIdValues(): bool
    {
        return $this->allowNonIdValues;
    }

    /**
     * @param bool $allowNonIdValues
     */
    public function setAllowNonIdValues(bool $allowNonIdValues): void
    {
        $this->allowNonIdValues = $allowNonIdValues;
    }

    /**
     * @return bool
     */
    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * @param bool $multiple
     */
    public function setMultiple(bool $multiple): void
    {
        $this->multiple = $multiple;
    }
}