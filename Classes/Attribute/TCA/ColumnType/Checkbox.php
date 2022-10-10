<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Attribute\TCA\ColumnType;

/**
 * Class Checkbox
 *
 * @package PSB\PsbFoundation\Attribute\TCA\ColumnType
 */
class Checkbox extends AbstractColumnType
{
    public const RENDER_TYPES = [
        'CHECKBOX_LABELED_TOGGLE' => 'checkboxLabeledToggle',
        'CHECKBOX_TOGGLE'         => 'checkboxToggle',
        'DEFAULT'                 => 'default',
    ];

    /**
     * @param int    $default    https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Check/Properties/Default.html
     * @param array  $items      https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Check/Properties/Items.html
     * @param string $renderType https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Check/Properties/RenderType.html
     */
    public function __construct(
        protected int $default = 0,
        protected array $items = [],
        protected string $renderType = self::RENDER_TYPES['CHECKBOX_TOGGLE'],
    ) {
    }

    /**
     * @return int
     */
    public function getDefault(): int
    {
        return $this->default;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return string
     */
    public function getRenderType(): string
    {
        return $this->renderType;
    }

    /**
     * @param array $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }
}
