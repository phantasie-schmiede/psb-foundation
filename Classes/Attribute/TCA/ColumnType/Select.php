<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Attribute\TCA\ColumnType;

use Attribute;
use PSB\PsbFoundation\Service\Configuration\TcaService;
use PSB\PsbFoundation\Traits\PropertyInjection\ExtensionInformationServiceTrait;
use PSB\PsbFoundation\Utility\ValidationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Select
 *
 * @package PSB\PsbFoundation\Attribute\TCA\ColumnType
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Select extends AbstractColumnType
{
    use ExtensionInformationServiceTrait;

    public const EMPTY_DEFAULT_ITEM = [
        [
            'LLL:EXT:psb_foundation/Resources/Private/Language/Backend/Classes/Attribute/TCA/select.xlf:pleaseChoose',
            0,
        ],
    ];

    public const RENDER_TYPES = [
        'SELECT_CHECK_BOX'             => 'selectCheckBox',
        'SELECT_MULTIPLE_SIDE_BY_SIDE' => 'selectMultipleSideBySide',
        'SELECT_SINGLE'                => 'selectSingle',
        'SELECT_SINGLE_BOX'            => 'selectSingleBox',
        'SELECT_TREE'                  => 'selectTree',
    ];

    /**
     * @var TcaService
     */
    protected TcaService $tcaService;

    /**
     * @param bool|null   $allowNonIdValues
     * @param int|null    $autoSizeMax
     * @param string|null $eval
     * @param array|null  $fieldControl
     * @param bool|null   $fieldControlDisableAddRecord
     * @param bool|null   $fieldControlDisableEditPopup
     * @param bool|null   $fieldControlDisableListModule
     * @param string|null $foreignTable            Instead of directly specifying a foreign table, it is possible to
     *                                             specify a domain model class via linkedModel.
     * @param string|null $foreignTableWhere
     * @param array|null  $items
     * @param string|null $itemsProcFunc
     * @param string|null $linkedModel             Instead of directly specifying a foreign table, it is possible to
     *                                             specify a domain model class.
     * @param int         $maxItems
     * @param int|null    $minItems
     * @param bool|null   $multiple
     * @param array|null  $prependItem
     * @param string      $renderType
     * @param int|null    $size
     * @param array|null  $treeConfig
     * @param string|null $treeConfigChildrenField You can use the property name. It will be converted to the column
     *                                             name automatically.
     * @param string|null $treeConfigDataProvider
     * @param bool|null   $treeConfigExpandAll
     * @param int|null    $treeConfigMaxLevels
     * @param string|null $treeConfigNonSelectableLevels
     * @param string|null $treeConfigParentField   You can use the property name. It will be converted to the column
     *                                             name automatically.
     * @param bool|null   $treeConfigShowHeader
     * @param string|null $treeConfigStartingPoints
     */
    public function __construct(
        protected ?bool $allowNonIdValues = null,
        protected ?int $autoSizeMax = 1,
        protected ?string $eval = null,
        protected ?array $fieldControl = null,
        protected ?bool $fieldControlDisableAddRecord = null,
        protected ?bool $fieldControlDisableEditPopup = null,
        protected ?bool $fieldControlDisableListModule = null,
        protected ?string $foreignTable = null,
        protected ?string $foreignTableWhere = null,
        protected ?array $items = null,
        protected ?string $itemsProcFunc = null,
        protected ?string $linkedModel = null,
        protected int $maxItems = 1,
        protected ?int $minItems = null,
        protected ?bool $multiple = null,
        protected ?array $prependItem = null,
        protected string $renderType = self::RENDER_TYPES['SELECT_SINGLE'],
        protected ?int $size = 1,
        protected ?array $treeConfig = null,
        protected ?string $treeConfigChildrenField = null,
        protected ?string $treeConfigDataProvider = null,
        protected ?bool $treeConfigExpandAll = null,
        protected ?int $treeConfigMaxLevels = null,
        protected ?string $treeConfigNonSelectableLevels = null,
        protected ?string $treeConfigParentField = null,
        protected ?bool $treeConfigShowHeader = null,
        protected ?string $treeConfigStartingPoints = null,
    ) {
        $this->tcaService = GeneralUtility::makeInstance(TcaService::class);

        if (class_exists($linkedModel)) {
            $this->foreignTable = $this->tcaService->convertClassNameToTableName($linkedModel);
        }

        ValidationUtility::checkValueAgainstConstant(self::RENDER_TYPES, $renderType);

        if (self::RENDER_TYPES['SELECT_TREE'] === $renderType) {
            $this->autoSizeMax = null;
            $this->size = null;
        }
    }

    /**
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/CommonProperties/AutoSizeMax.html
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
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/CommonProperties/FieldControl.html#tca-property-fieldcontrol
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
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Select/Properties/ForeignTable.html
     * @return string|null
     */
    public function getForeignTable(): ?string
    {
        return $this->foreignTable;
    }

    /**
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Select/Properties/ForeignTableWhere.html
     * @return string|null
     */
    public function getForeignTableWhere(): ?string
    {
        return $this->foreignTableWhere;
    }

    /**
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Select/Properties/Items.html
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
     * @link https://docs.typo3.org/m/typo3/reference-tca/11.5/en-us/ColumnsConfig/CommonProperties/ItemsProcFunc.html
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
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/CommonProperties/Minitems.html#tca-property-minitems
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
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/CommonProperties/Size.html#tca-property-size
     * @return int|null
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Select/Properties/SelectTreeConfig.html
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
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Select/Properties/AllowNonIdValues.html
     * @return bool|null
     */
    public function isAllowNonIdValues(): ?bool
    {
        return $this->allowNonIdValues;
    }

    /**
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/CommonProperties/Multiple.html#tca-property-multiple
     * @return bool|null
     */
    public function isMultiple(): ?bool
    {
        return $this->multiple;
    }
}
