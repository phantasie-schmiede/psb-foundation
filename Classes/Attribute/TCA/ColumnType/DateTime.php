<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Attribute\TCA\ColumnType;

use PSB\PsbFoundation\Utility\StringUtility;
use function is_string;

/**
 * Class DateTime
 *
 * @package PSB\PsbFoundation\Attribute\TCA\ColumnType
 */
class DateTime extends AbstractColumnType
{
    /**
     * @param string                $dbType            https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/DateTime/Properties/DbType.html
     * @param bool|null             $disableAgeDisplay https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Datetime/Properties/DisableAgeDisplay.html
     * @param \DateTime|string|null $rangeLower        https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/DateTime/Properties/Range.html
     * @param \DateTime|string|null $rangeUpper        https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/DateTime/Properties/Range.html
     */
    public function __construct(
        protected string $dbType = 'datetime',
        protected ?bool $disableAgeDisplay = null,
        protected \DateTime|string|null $rangeLower = null,
        protected \DateTime|string|null $rangeUpper = null,
    ) {
        if (is_string($rangeLower)) {
            $this->rangeLower = StringUtility::convertToDateTime($this->rangeLower);
        }

        if (is_string($rangeUpper)) {
            $this->rangeUpper = StringUtility::convertToDateTime($this->rangeUpper);
        }
    }

    /**
     * @return string|null
     */
    public function getDbType(): ?string
    {
        return $this->dbType;
    }

    /**
     * @return array|null
     */
    public function getRange(): ?array
    {
        if (null !== $this->rangeLower) {
            $range['lower'] = $this->rangeLower;
        }

        if (null !== $this->rangeUpper) {
            $range['upper'] = $this->rangeUpper;
        }

        return $range ?? null;
    }
}
