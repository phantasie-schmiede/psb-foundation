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

/**
 * Class Checkbox
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA\Column
 */
class Checkbox extends AbstractColumnAnnotation
{
    public const RENDER_TYPES = [
        'CHECKBOX_LABELED_TOGGLE' => 'checkboxLabeledToggle',
        'CHECKBOX_TOGGLE'         => 'checkboxToggle',
        'DEFAULT'                 => 'default',
    ];

    public const TYPE = self::TYPES['CHECKBOX'];

    /**
     * @var int
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Check/Properties/Default.html
     */
    protected $default = 0;

    /**
     * @var array
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Check/Properties/Items.html
     */
    protected array $items = [];

    /**
     * @var string
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Check/Properties/RenderType.html
     */
    protected string $renderType = self::RENDER_TYPES['CHECKBOX_TOGGLE'];

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
     * @param bool|int $default
     */
    public function setDefault($default): void
    {
        $this->default = (int)$default;
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
        $this->renderType = $renderType;
    }
}
