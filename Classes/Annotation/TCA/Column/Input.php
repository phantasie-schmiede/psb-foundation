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
 * Class Input
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA\Column
 */
class Input extends AbstractColumnAnnotation
{
    public const TYPE = self::TYPES['INPUT'];

    /**
     * @var string
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Eval.html
     */
    protected string $eval = 'trim';

    /**
     * @var int|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Max.html
     */
    protected ?int $max = null;

    /**
     * @var string|null
     */
    protected ?string $renderType = null;

    /**
     * @var int
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Size.html
     */
    protected int $size = 20;

    /**
     * @return string
     */
    public function getEval(): string
    {
        return $this->eval;
    }

    /**
     * @param string $eval
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
     */
    public function setSize(int $size): void
    {
        $this->size = $size;
    }
}
