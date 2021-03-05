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

use PSB\PsbFoundation\Service\Configuration\Fields;
use PSB\PsbFoundation\Traits\PropertyInjection\ExtensionInformationServiceTrait;
use PSB\PsbFoundation\Utility\ValidationUtility;

/**
 * Class Select
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA
 */
class Select extends AbstractTcaFieldAnnotation
{
    use ExtensionInformationServiceTrait;

    public const EMPTY_DEFAULT_ITEM = [
        [
            'LLL:EXT:psb_foundation/Resources/Private/Language/Backend/Service/DocComment/Annotations/TCA/select.xlf:pleaseChoose',
            0,
        ],
    ];

    public const RENDER_TYPES = [
        'SELECT_SINGLE'                => 'selectSingle',
        'SELECT_SINGLE_BOX'            => 'selectSingleBox',
        'SELECT_CHECK_BOX'             => 'selectCheckBox',
        'SELECT_MULTIPLE_SIDE_BY_SIDE' => 'selectMultipleSideBySide',
        'SELECT_TREE'                  => 'selectTree',
    ];

    public const TYPE = Fields::FIELD_TYPES['SELECT'];

    /**
     * @var int
     */
    protected int $autoSizeMax = 1;

    /**
     * @var string|null
     */
    protected ?string $eval = null;

    /**
     * @var array
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
     */
    protected ?string $foreignTable = null;

    /**
     * @var string|null
     */
    protected ?string $foreignTableWhere = null;

    /**
     * @var array
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
     * @var bool
     */
    protected bool $multiple = false;

    /**
     * @var string
     */
    protected string $renderType = self::RENDER_TYPES['SELECT_SINGLE'];

    /**
     * @var int
     */
    protected int $size = 1;

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
