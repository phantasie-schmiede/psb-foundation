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
use PSB\PsbFoundation\Enum\CheckboxRenderType;
use PSB\PsbFoundation\Exceptions\MisconfiguredTcaException;

/**
 * Class Checkbox
 *
 * @package PSB\PsbFoundation\Attribute\TCA\ColumnType
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Checkbox extends AbstractColumnType
{
    public const DATABASE_DEFINITION = 'tinyint(4) DEFAULT \'0\'';

    /**
     * The parameters $maximumRecordsChecked and $maximumRecordsCheckedInPid are used for the TCA properties eval and
     * validation.
     *
     * @param int|string         $cols               https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Check/Properties/Cols.html
     * @param string             $eval               https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Check/Properties/Eval.html
     * @param false|boolean      $invertStateDisplay https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Check/Properties/InvertStateDisplay.html
     * @param array              $items              https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Check/Properties/Items.html
     * @param int                $maximumRecordsChecked
     * @param int                $maximumRecordsCheckedInPid
     * @param CheckboxRenderType $renderType         https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Check/Properties/RenderType.html
     * @param array|null         $validation         https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Check/Properties/Validation.html
     *
     * @throws MisconfiguredTcaException
     */
    public function __construct(
        protected int|string         $cols = 1,
        protected string             $eval = '',
        protected bool               $invertStateDisplay = false,
        protected array              $items = [],
        protected int                $maximumRecordsChecked = 0,
        protected int                $maximumRecordsCheckedInPid = 0,
        protected CheckboxRenderType $renderType = CheckboxRenderType::checkboxToggle,
        protected array|null         $validation = null,
    ) {
        if (!is_int($cols) && 'inline' !== $cols) {
            throw new MisconfiguredTcaException(__CLASS__ . ': Invalid value for "cols"! (' . $cols . ')', 1681830487);
        }
    }

    /**
     * @return int|string
     */
    public function getCols(): int|string
    {
        return $this->cols;
    }

    /**
     * @return string|null
     */
    public function getEval(): ?string
    {
        $validation = null;

        if (!empty($this->validation)) {
            $validation = [$this->validation];
        }

        if (0 < $this->maximumRecordsChecked) {
            $validation[] = 'maximumRecordsChecked';
        }

        if (0 < $this->maximumRecordsCheckedInPid) {
            $validation[] = 'maximumRecordsCheckedInPid';
        }

        return $validation ? implode(', ', $validation) : null;
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
        return $this->renderType->value;
    }

    /**
     * @return array|null
     */
    public function getValidation(): ?array
    {
        $validation = null;

        if (0 < $this->maximumRecordsChecked) {
            $validation['maximumRecordsChecked'] = $this->maximumRecordsChecked;
        }

        if (0 < $this->maximumRecordsCheckedInPid) {
            $validation['maximumRecordsCheckedInPid'] = $this->maximumRecordsCheckedInPid;
        }

        return $validation;
    }

    /**
     * @return bool
     */
    public function isInvertStateDisplay(): bool
    {
        return $this->invertStateDisplay;
    }

    /**
     * @param array $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }
}
