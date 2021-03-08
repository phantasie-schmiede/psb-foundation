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
 * Class Input
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA
 */
class Input extends AbstractTcaFieldAnnotation
{
    public const TYPE = Fields::FIELD_TYPES['INPUT'];

    /**
     * @var int|string|null
     */
    protected $default = null;

    /**
     * @var string
     */
    protected string $eval = 'trim';

    /**
     * @var int|null
     */
    protected ?int $max = null;

    /**
     * @var string|null
     */
    protected ?string $renderType = null;

    /**
     * @var int
     */
    protected int $size = 20;

    /**
     * @return int|string|null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param int|string|null $default
     */
    public function setDefault($default): void
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
     * @return int|null
     */
    public function getMax(): ?int
    {
        return $this->max;
    }

    /**
     * @param int|null $max
     */
    public function setMax(?int $max): void
    {
        $this->max = $max;
    }

    /**
     * @return string|null
     */
    public function getRenderType(): ?string
    {
        return $this->renderType;
    }

    /**
     * @param string|null $renderType
     */
    public function setRenderType(?string $renderType): void
    {
        $this->renderType = $renderType;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize(int $size): void
    {
        $this->size = $size;
    }
}
