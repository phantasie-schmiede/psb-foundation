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
 * Class Text
 *
 * @package PSB\PsbFoundation\Attribute\TCA\ColumnType
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Text extends AbstractColumnType
{
    /**
     * @param int       $cols
     * @param bool|null $enableRichText
     * @param string    $eval
     * @param int       $rows
     */
    public function __construct(
        protected int $cols = 32,
        protected ?bool $enableRichText = null,
        protected string $eval = 'trim',
        protected int $rows = 5,
    ) {
    }

    /**
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Text/Properties/Cols.html
     * @return int
     */
    public function getCols(): int
    {
        return $this->cols;
    }

    /**
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Text/Properties/Eval.html
     * @return string
     */
    public function getEval(): string
    {
        return $this->eval;
    }

    /**
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Text/Properties/Rows.html
     * @return int
     */
    public function getRows(): int
    {
        return $this->rows;
    }

    /**
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Text/Properties/EnableRichtext.html
     * @return bool|null
     */
    public function isEnableRichText(): ?bool
    {
        return $this->enableRichText;
    }
}
