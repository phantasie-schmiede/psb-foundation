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

namespace PSB\PsbFoundation\Annotation\TCA;

/**
 * Class Mm
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA
 */
class Mm extends Select
{
    /**
     * @var int
     */
    protected int $autoSizeMax = 30;

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
     * @var int
     */
    protected int $size = 10;

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

    /**
     * @param string $mm
     */
    public function setMm(string $mm): void
    {
        $this->mm = $mm;
    }

    /**
     * @param bool|null $mmHasUidField
     */
    public function setMmHasUidField(?bool $mmHasUidField): void
    {
        $this->mmHasUidField = $mmHasUidField;
    }

    /**
     * @param array|null $mmInsertFields
     */
    public function setMmInsertFields(?array $mmInsertFields): void
    {
        $this->mmInsertFields = $mmInsertFields;
    }

    /**
     * @param array|null $mmMatchFields
     */
    public function setMmMatchFields(?array $mmMatchFields): void
    {
        $this->mmMatchFields = $mmMatchFields;
    }

    /**
     * @param string|null $mmOppositeField
     */
    public function setMmOppositeField(?string $mmOppositeField): void
    {
        $this->mmOppositeField = $mmOppositeField;
    }
}
