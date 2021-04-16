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

use PSB\PsbFoundation\Service\Configuration\Fields;

/**
 * Class Text
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA
 */
class Text extends AbstractTcaFieldAnnotation
{
    public const TYPE = Fields::FIELD_TYPES['TEXT'];

    /**
     * @var int
     */
    protected int $cols = 32;

    /**
     * @var string|null
     */
    protected ?string $default = null;

    /**
     * @var bool
     */
    protected bool $enableRichtext = false;

    /**
     * @var string
     */
    protected string $eval = 'trim';

    /**
     * @var int
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
     * @return string|null
     */
    public function getDefault(): ?string
    {
        return $this->default;
    }

    /**
     * @param string|null $default
     */
    public function setDefault(?string $default): void
    {
        $this->default = $default;
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
    public function isEnableRichtext(): bool
    {
        return $this->enableRichtext;
    }

    /**
     * @param bool $enableRichtext
     */
    public function setEnableRichtext(bool $enableRichtext): void
    {
        $this->enableRichtext = $enableRichtext;
    }
}
