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
 * Class DateTime
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA\Column
 */
class DateTime extends Input
{
    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/DateTime/Properties/DbType.html
     */
    protected ?string $dbType = 'datetime';

    /**
     * @var string
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/DateTime/Properties/Eval.html
     */
    protected string $eval = 'datetime, null';

    /**
     * @var \DateTime|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/DateTime/Properties/Range.html
     */
    protected ?\DateTime $rangeLower = null;

    /**
     * @var \DateTime|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/DateTime/Properties/Range.html
     */
    protected ?\DateTime $rangeUpper = null;

    /**
     * @var string|null
     */
    protected ?string $renderType = 'inputDateTime';

    /**
     * @var int
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Size.html
     */
    protected int $size = 12;

    /**
     * @param string $rangeLower
     *
     * @return void
     */
    public function setRangeLower(string $rangeLower): void
    {
        $timestamp = strtotime($rangeLower);
        $this->rangeLower = (new \DateTime())->setTimestamp($timestamp);
    }

    /**
     * @param string $rangeUpper
     *
     * @return void
     */
    public function setRangeUpper(string $rangeUpper): void
    {
        $timestamp = strtotime($rangeUpper);
        $this->rangeUpper = (new \DateTime())->setTimestamp($timestamp);
    }

    /**
     * @return string|null
     */
    public function getDbType(): ?string
    {
        return $this->dbType;
    }

    /**
     * @param string|null $dbType
     */
    public function setDbType(?string $dbType): void
    {
        $this->dbType = $dbType;
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
}
