<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Annotation\TCA\Column;

/**
 * Class Integer
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA\Column
 */
class Integer extends Input
{
    /**
     * @var string
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Eval.html
     */
    protected string $eval = 'int';

    /**
     * @var int|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Range.html
     */
    protected ?int $rangeLower = null;

    /**
     * @var int|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Range.html
     */
    protected ?int $rangeUpper = null;

    /**
     * @var int|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Slider.html
     */
    protected ?int $sliderStep = null;

    /**
     * @var int|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Slider.html
     */
    protected ?int $sliderWidth = null;

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
     * @param int $rangeLower
     *
     * @return void
     */
    public function setRangeLower(int $rangeLower): void
    {
        $this->rangeLower = $rangeLower;
    }

    /**
     * @param int $rangeUpper
     *
     * @return void
     */
    public function setRangeUpper(int $rangeUpper): void
    {
        $this->rangeUpper = $rangeUpper;
    }

    /**
     * @param int|null $sliderStep
     *
     * @return void
     */
    public function setSliderStep(?int $sliderStep): void
    {
        $this->sliderStep = $sliderStep;
    }

    /**
     * @param int|null $sliderWidth
     *
     * @return void
     */
    public function setSliderWidth(?int $sliderWidth): void
    {
        $this->sliderWidth = $sliderWidth;
    }
}
