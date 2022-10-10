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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Inline
 *
 * @package PSB\PsbFoundation\Attribute\TCA\ColumnType
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Inline extends AbstractColumnType
{
    /**
     * @var TcaService
     */
    protected TcaService $tcaService;

    /**
     * @param array|null  $appearance         https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Inline/Properties/Appearance.html
     * @param string|null $foreignField       https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Inline/Properties/ForeignField.html
     * @param array|null  $foreignMatchFields https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Inline/Properties/ForeignMatchFields.html
     * @param string|null $foreignSortBy      https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Inline/Properties/ForeignSortby.html#confval-foreign_sortby
     * @param string|null $foreignTable       https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Inline/Properties/ForeignTable.html
     * @param string|null $linkedModel        Instead of directly specifying a foreign table, it is possible to specify
     *                                        a domain model class.
     * @param int|null    $maxItems           https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/CommonProperties/Maxitems.html
     * @param string|null $mm                 https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Inline/Properties/Mm.html
     * @param array|null  $mmMatchFields
     * @param string|null $mmOppositeField    https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Inline/Properties/Mm.html#confval-MM_opposite_field-type-inline
     */
    public function __construct(
        protected ?array $appearance = [
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
        ],
        protected ?string $foreignField = null,
        protected ?array $foreignMatchFields = null,
        protected ?string $foreignSortBy = null,
        protected ?string $foreignTable = null,
        protected ?string $linkedModel = null,
        protected ?int $maxItems = null,
        protected ?string $mm = null,
        protected ?array $mmMatchFields = null,
        protected ?string $mmOppositeField = null,
    ) {
        $this->tcaService = GeneralUtility::makeInstance(TcaService::class);

        if (class_exists($linkedModel)) {
            $this->foreignTable = $this->tcaService->convertClassNameToTableName($linkedModel);
        }
    }

    /**
     * @return array
     */
    public function getAppearance(): array
    {
        return $this->appearance;
    }

    /**
     * @return string|null
     */
    public function getForeignField(): ?string
    {
        if (null === $this->foreignField) {
            return null;
        }

        return $this->tcaService->convertPropertyNameToColumnName($this->foreignField, $this->linkedModel);
    }

    /**
     * @return array|null
     */
    public function getForeignMatchFields(): ?array
    {
        return $this->foreignMatchFields;
    }

    /**
     * @return string|null
     */
    public function getForeignSortBy(): ?string
    {
        return $this->foreignSortBy;
    }

    /**
     * @return string
     */
    public function getForeignTable(): string
    {
        return $this->foreignTable;
    }

    /**
     * @return int|null
     */
    public function getMaxItems(): ?int
    {
        return $this->maxItems;
    }

    /**
     * @return string|null
     */
    public function getMm(): ?string
    {
        return $this->mm;
    }

    /**
     * @return array|null
     */
    public function getMmMatchFields(): ?array
    {
        return $this->mmMatchFields;
    }

    /**
     * @return string|null
     */
    public function getMmOppositeField(): ?string
    {
        return $this->mmOppositeField;
    }
}
