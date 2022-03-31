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
 * Class Text
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA\Column
 */
class Text extends AbstractColumnAnnotation
{
    public const TYPE = self::TYPES['TEXT'];

    /**
     * @var int
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Text/Properties/Cols.html
     */
    protected int $cols = 32;

    /**
     * @var string|null
     */
    protected $default;

    /**
     * @var bool
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Text/Properties/EnableRichtext.html
     */
    protected bool $enableRichText = false;

    /**
     * @var string
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Text/Properties/Eval.html
     */
    protected string $eval = 'trim';

    /**
     * @var int
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Text/Properties/Rows.html
     */
    protected int $rows = 5;

    /**
     * @return int
     */
    public function getCols(): int
    {
        return $this->cols;
    }

    /**
     * @param int $cols
     */
    public function setCols(int $cols): void
    {
        $this->cols = $cols;
    }

    /**
     * @return string
     */
    public function getEval(): string
    {
        return $this->eval;
    }

    /**
     * @param string $eval
     */
    public function setEval(string $eval): void
    {
        $this->eval = $eval;
    }

    /**
     * @return int
     */
    public function getRows(): int
    {
        return $this->rows;
    }

    /**
     * @param int $rows
     */
    public function setRows(int $rows): void
    {
        $this->rows = $rows;
    }

    /**
     * @return bool
     */
    public function isEnableRichText(): bool
    {
        return $this->enableRichText;
    }

    /**
     * @param bool $enableRichText
     */
    public function setEnableRichText(bool $enableRichText): void
    {
        $this->enableRichText = $enableRichText;
    }
}
