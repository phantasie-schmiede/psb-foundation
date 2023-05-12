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
    public const DATABASE_DEFINITION = 'text';

    /**
     * @param int       $cols           https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Text/Properties/Cols.html
     * @param bool|null $enableRichText https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Text/Properties/EnableRichtext.html
     * @param string    $eval           https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Text/Properties/Eval.html
     * @param int       $rows           https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Text/Properties/Rows.html
     */
    public function __construct(
        protected int    $cols = 32,
        protected ?bool  $enableRichText = null,
        protected string $eval = 'trim',
        protected int    $rows = 5,
    ) {
    }

    /**
     * @return int
     */
    public function getCols(): int
    {
        return $this->cols;
    }

    /**
     * @return string
     */
    public function getEval(): string
    {
        return $this->eval;
    }

    /**
     * @return int
     */
    public function getRows(): int
    {
        return $this->rows;
    }

    /**
     * @return bool|null
     */
    public function isEnableRichText(): ?bool
    {
        return $this->enableRichText;
    }
}
