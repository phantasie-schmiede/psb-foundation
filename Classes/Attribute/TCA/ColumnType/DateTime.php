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
use PSB\PsbFoundation\Enum\DateType;
use PSB\PsbFoundation\Utility\StringUtility;
use function is_string;

/**
 * Class DateTime
 *
 * Fields of this type will be added to the database by TYPO3 automatically.
 * There is no need to manually add them in ext_tables.sql!
 *
 * @package PSB\PsbFoundation\Attribute\TCA\ColumnType
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class DateTime extends AbstractColumnType
{
    /**
     * @param DateType|null         $dbType            https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Datetime/Properties/DbType.html
     * @param bool|null             $disableAgeDisplay https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Datetime/Properties/DisableAgeDisplay.html
     * @param DateType              $format            https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Datetime/Properties/Format.html
     * @param bool                  $nullable          https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Datetime/Properties/Nullable.html
     * @param array                 $range             https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Datetime/Properties/Range.html
     * @param \DateTime|string|null $rangeLower
     * @param \DateTime|string|null $rangeUpper
     */
    public function __construct(
        protected ?DateType             $dbType = null,
        protected ?bool                 $disableAgeDisplay = null,
        protected DateType              $format = DateType::datetime,
        protected bool                  $nullable = false,
        protected array                 $range = [],
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
     * @return string
     */
    public function getDbType(): string
    {
        return $this->dbType?->value ?? $this->format->value;
    }

    /**
     * @return bool|null
     */
    public function getDisableAgeDisplay(): ?bool
    {
        return $this->disableAgeDisplay;
    }

    /**
     * @return DateType
     */
    public function getFormat(): DateType
    {
        return $this->format;
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

    /**
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }
}
