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

/**
 * Class Inline
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA\Column
 */
class Inline extends AbstractColumnAnnotation
{
    use ExtensionInformationServiceTrait;

    public const TYPE = self::TYPES['INLINE'];

    /**
     * @var array
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Inline/Properties/Appearance.html
     */
    protected array $appearance = [
        'collapseAll'                     => true,
        'enabledControls'                 => [
            'dragdrop' => true,
        ],
        'expandSingle'                    => true,
        'levelLinksPosition'              => 'bottom',
        'showAllLocalizationLink'         => true,
        'showPossibleLocalizationRecords' => true,
        'showSynchronizationLink'         => true,
        'useSortable'                     => true,
    ];

    /**
     * You can use the property name. It will be converted to the column name automatically.
     *
     * @var string
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Inline/Properties/ForeignField.html
     */
    protected string $foreignField = '';

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Inline/Properties/ForeignSortby.html#confval-foreign_sortby
     */
    protected ?string $foreignSortBy = null;

    /**
     * @var string
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Inline/Properties/ForeignTable.html
     */
    protected string $foreignTable = '';

    /**
     * Instead of directly specifying a foreign table, it is possible to specify a domain model class.
     *
     * @var string|null
     */
    protected ?string $linkedModel = null;

    /**
     * @var int|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/CommonProperties/Maxitems.html
     */
    protected ?int $maxItems = null;

    /**
     * name of the mm-table
     *
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Inline/Properties/Mm.html
     */
    protected ?string $mm = null;

    /**
     * @var array|null
     */
    protected ?array $mmMatchFields = null;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Inline/Properties/Mm.html#confval-MM_opposite_field
     */
    protected ?string $mmOppositeField = null;

    /**
     * @return array
     */
    public function getAppearance(): array
    {
        return $this->appearance;
    }

    /**
     * @param array $appearance
     */
    public function setAppearance(array $appearance): void
    {
        $this->appearance = $appearance;
    }

    /**
     * @return string
     */
    public function getForeignField(): string
    {
        return $this->tcaService->convertPropertyNameToColumnName($this->foreignField);
    }

    /**
     * @param string $foreignField
     */
    public function setForeignField(string $foreignField): void
    {
        $this->foreignField = $foreignField;
    }

    /**
     * @return string|null
     */
    public function getForeignSortBy(): ?string
    {
        return $this->foreignSortBy;
    }

    /**
     * @param string|null $foreignSortBy
     */
    public function setForeignSortBy(?string $foreignSortBy): void
    {
        $this->foreignSortBy = $foreignSortBy;
    }

    /**
     * @return string
     */
    public function getForeignTable(): string
    {
        return $this->foreignTable;
    }

    /**
     * @param string $foreignTable
     */
    public function setForeignTable(string $foreignTable): void
    {
        $this->foreignTable = $foreignTable;
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
     * @return int|null
     */
    public function getMaxItems(): ?int
    {
        return $this->maxItems;
    }

    /**
     * @param int|null $maxItems
     */
    public function setMaxItems(?int $maxItems): void
    {
        $this->maxItems = $maxItems;
    }

    /**
     * @return string|null
     */
    public function getMm(): ?string
    {
        return $this->mm;
    }

    /**
     * @param string|null $mm
     */
    public function setMm(?string $mm): void
    {
        $this->mm = $mm;
    }

    /**
     * @return array|null
     */
    public function getMmMatchFields(): ?array
    {
        return $this->mmMatchFields;
    }

    /**
     * @param array|null $mmMatchFields
     */
    public function setMmMatchFields(?array $mmMatchFields): void
    {
        $this->mmMatchFields = $mmMatchFields;
    }

    /**
     * @return string|null
     */
    public function getMmOppositeField(): ?string
    {
        return $this->mmOppositeField;
    }

    /**
     * @param string|null $mmOppositeField
     */
    public function setMmOppositeField(?string $mmOppositeField): void
    {
        $this->mmOppositeField = $mmOppositeField;
    }
}
