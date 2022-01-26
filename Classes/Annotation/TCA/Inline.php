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

use PSB\PsbFoundation\Traits\PropertyInjection\ExtensionInformationServiceTrait;

/**
 * Class Inline
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA
 */
class Inline extends AbstractTcaFieldAnnotation
{
    use ExtensionInformationServiceTrait;

    public const TYPE = self::TYPES['INLINE'];

    /**
     * @var array
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
     * @var string
     */
    protected string $foreignField = '';

    /**
     * @var string|null
     */
    protected ?string $foreignSortBy = null;

    /**
     * @var string
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
     */
    protected ?int $maxItems = null;

    /**
     * name of the mm-table
     *
     * @var string|null
     */
    protected ?string $mm = null;

    /**
     * @var array|null
     */
    protected ?array $mmMatchFields = null;

    /**
     * @var string|null
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
     * @return string|null
     */
    public function getLinkedModel(): ?string
    {
        return $this->linkedModel;
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
