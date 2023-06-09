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
use PSB\PsbFoundation\Enum\NumberFormat;

/**
 * Class Number
 *
 * @package PSB\PsbFoundation\Attribute\TCA\ColumnType
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Number extends AbstractColumnType
{
    public const DATABASE_DEFINITIONS = [
        'DECIMAL'          => 'double(11,2) DEFAULT \'0.00\'',
        'INTEGER_SIGNED'   => 'int(11) DEFAULT \'0\'',
        'INTEGER_UNSIGNED' => 'int(11) unsigned DEFAULT \'0\'',
    ];

    /**
     * @param bool|null    $autocomplete https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Number/Properties/Autocomplete.html
     * @param NumberFormat $format       https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Number/Properties/Format.html
     * @param int|null     $rangeLower   https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Range.html
     * @param int|null     $rangeUpper   https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Range.html
     * @param int|null     $sliderStep   https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Slider.html
     * @param int|null     $sliderWidth  https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Slider.html
     * @param array|null   $valuePicker  https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Number/Properties/ValuePicker.html
     */
    public function __construct(
        protected ?bool        $autocomplete = null,
        protected NumberFormat $format = NumberFormat::integer,
        protected ?int         $rangeLower = null,
        protected ?int         $rangeUpper = null,
        protected ?int         $sliderStep = null,
        protected ?int         $sliderWidth = null,
        protected ?array       $valuePicker = null,
    ) {
    }

    /**
     * @return bool|null
     */
    public function getAutocomplete(): ?bool
    {
        return $this->autocomplete;
    }

    /**
     * @return string
     */
    public function getDatabaseDefinition(): string
    {
        if (NumberFormat::decimal === $this->format) {
            return self::DATABASE_DEFINITIONS['DECIMAL'];
        }

        if (NumberFormat::integer === $this->format && 0 < (int)$this->rangeLower) {
            return self::DATABASE_DEFINITIONS['INTEGER_UNSIGNED'];
        }

        return self::DATABASE_DEFINITIONS['INTEGER_SIGNED'];
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format->value;
    }

    /**
     * @return array|null
     */
    public function getRange(): ?array
    {
        $range = null;

        if (null !== $this->rangeLower) {
            $range['lower'] = $this->rangeLower;
        }

        if (null !== $this->rangeUpper) {
            $range['upper'] = $this->rangeUpper;
        }

        return $range;
    }

    /**
     * @return array|null
     */
    public function getSlider(): ?array
    {
        $sliderConfiguration = null;

        if (null !== $this->sliderStep) {
            $sliderConfiguration['step'] = $this->sliderStep;
        }

        if (null !== $this->sliderWidth) {
            $sliderConfiguration['width'] = $this->sliderWidth;
        }

        return $sliderConfiguration;
    }

    /**
     * @return array|null
     */
    public function getValuePicker(): ?array
    {
        if (null === $this->valuePicker) {
            return null;
        }

        return ['items' => $this->valuePicker];
    }
}
