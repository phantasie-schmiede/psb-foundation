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
 * Class Checkbox
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA
 */
class Checkbox extends AbstractTcaFieldAnnotation
{
    public const RENDER_TYPES = [
        'CHECKBOX_LABELED_TOGGLE' => 'checkboxLabeledToggle',
        'CHECKBOX_TOGGLE'         => 'checkboxToggle',
        'DEFAULT'                 => 'default',
    ];

    public const TYPE = self::TYPES['CHECKBOX'];

    /**
     * @var int
     */
    protected int $default = 0;

    /**
     * @var array
     */
    protected array $items = [];

    /**
     * @var string
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
     * @return int
     */
    public function getDefault(): int
    {
        return $this->default;
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
