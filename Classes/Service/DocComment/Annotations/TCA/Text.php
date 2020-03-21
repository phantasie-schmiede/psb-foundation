<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Service\DocComment\Annotations\TCA;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use PSB\PsbFoundation\Service\Configuration\Fields;

/**
 * Class Text
 *
 * @Annotation
 * @package PSB\PsbFoundation\Service\DocComment\Annotations\TCA
 */
class Text extends AbstractTcaFieldAnnotation
{
    public const TYPE = Fields::FIELD_TYPES['TEXT'];

    /**
     * @var int
     */
    protected int $cols = 32;

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
}
