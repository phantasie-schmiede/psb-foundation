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
 * Class Input
 *
 * @package PSB\PsbFoundation\Attribute\TCA\ColumnType
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Input extends AbstractColumnType
{
    public const DATABASE_DEFINITION = AbstractColumnType::DATABASE_DEFINITIONS['STRING'];

    /**
     * @param string     $eval        https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Eval.html
     * @param int|null   $max         https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Max.html
     * @param int|null   $min         https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Min.html
     * @param int        $size        https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Size.html
     * @param array|null $valuePicker https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/ValuePicker.html
     */
    public function __construct(
        protected string $eval = 'trim',
        protected ?int $max = null,
        protected ?int $min = null,
        protected int $size = 20,
        protected ?array $valuePicker = null,
    ) {
    }

    /**
     * @return string
     */
    public function getEval(): string
    {
        return $this->eval;
    }

    /**
     * @return int|null
     */
    public function getMax(): ?int
    {
        return $this->max;
    }

    /**
     * @return int|null
     */
    public function getMin(): ?int
    {
        return $this->min;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return array|null
     */
    public function getValuePicker(): ?array
    {
        return $this->valuePicker;
    }
}
