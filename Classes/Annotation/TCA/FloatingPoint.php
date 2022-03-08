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
 * Class FloatingPoint
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA
 */
class FloatingPoint extends Input
{
    /**
     * @var string
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Eval.html
     */
    protected string $eval = 'double2';

    /**
     * @var float|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Range.html
     */
    protected ?float $rangeLower = null;

    /**
     * @var float|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Range.html
     */
    protected ?float $rangeUpper = null;

    /**
     * @var float|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Slider.html
     */
    protected ?float $sliderStep = null;

    /**
     * @var int|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Slider.html
     */
    protected ?int $sliderWidth = null;

    /**
     * @param float $rangeLower
     *
     * @return void
     */
    public function setRangeLower(float $rangeLower): void
    {
        $this->rangeLower = $rangeLower;
    }

    /**
     * @param float $rangeUpper
     *
     * @return void
     */
    public function setRangeUpper(float $rangeUpper): void
    {
        $this->rangeUpper = $rangeUpper;
    }

    /**
     * @param float|null $sliderStep
     */
    public function setSliderStep(?float $sliderStep): void
    {
        $this->sliderStep = $sliderStep;
    }

    /**
     * @param int|null $sliderWidth
     */
    public function setSliderWidth(?int $sliderWidth): void
    {
        $this->sliderWidth = $sliderWidth;
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
}
