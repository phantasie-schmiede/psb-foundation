<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Service\DocComment\Annotations\TCA;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use PSB\PsbFoundation\Service\Configuration\Fields;
use PSB\PsbFoundation\Utility\ExtensionInformationUtility;
use PSB\PsbFoundation\Utility\ValidationUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class Select
 *
 * @Annotation
 * @package PSB\PsbFoundation\Service\DocComment\Annotations\TCA
 */
class Select extends AbstractTcaFieldAnnotation
{
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
    protected string $renderType = 'selectSingle';

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
        if (ArrayUtility::isAssociative($this->items)) {
            // This is the case if the values are extracted from a constant.
            $items = [];

            foreach ($this->items as $item) {
                $items[] = [$item, $item];
            }

            $this->items = $items;
        }

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
     *
     * @throws Exception
     */
    public function setLinkedModel(string $linkedModel): void
    {
        $this->linkedModel = $linkedModel;

        if (class_exists($linkedModel)) {
            $this->setForeignTable(ExtensionInformationUtility::convertClassNameToTableName($linkedModel));
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
