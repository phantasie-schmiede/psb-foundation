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

/**
 * Class Mm
 *
 * @package PSB\PsbFoundation\Attribute\TCA\ColumnType
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Mm extends Select
{
    /**
     * @var int|null
     */
    protected ?int $autoSizeMax = 30;

    /**
     * 0 means no limit theoretically (max items allowed by core currently are 99999)
     *
     * @var int
     */
    protected int $maxItems = 0;

    /**
     * name of the mm-table
     *
     * @var string
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Select/Properties/Mm.html
     */
    protected string $mm = '';

    /**
     * @var bool|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Select/Properties/Mm.html#confval-MM_hasUidField
     */
    protected ?bool $mmHasUidField = null;

    /**
     * @var array|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Select/Properties/Mm.html#confval-MM_insert_fields
     */
    protected ?array $mmInsertFields = null;

    /**
     * @var array|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Select/Properties/Mm.html#confval-MM_match_fields
     */
    protected ?array $mmMatchFields = null;

    /**
     * You can use the property name. It will be converted to the column name automatically.
     *
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Select/Properties/Mm.html#confval-MM_opposite_field
     */
    protected ?string $mmOppositeField = null;

    /**
     * @var string
     */
    protected string $renderType = 'selectMultipleSideBySide';

    /**
     * @var int|null
     */
    protected ?int $size = 10;

    /**
     * @return string
     */
    public function getMm(): string
    {
        return $this->mm;
    }

    /**
     * @return bool|null
     */
    public function getMmHasUidField(): ?bool
    {
        return $this->mmHasUidField;
    }

    /**
     * @return array|null
     */
    public function getMmInsertFields(): ?array
    {
        return $this->mmInsertFields;
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
        if (null === $this->mmOppositeField) {
            return null;
        }

        return $this->tcaService->convertPropertyNameToColumnName($this->mmOppositeField);
    }
}
