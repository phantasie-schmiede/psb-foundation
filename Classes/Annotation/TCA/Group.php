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

/**
 * Class Group
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA
 */
class Group extends AbstractTcaFieldAnnotation
{
    public const TYPE = self::TYPES['GROUP'];

    /**
     * @var string
     */
    protected string $allowed = '*';

    /**
     * @var string
     */
    protected string $internalType = 'db';

    /**
     * @var int
     */
    protected int $maxItems = 1;

    /**
     * @var int
     */
    protected int $minItems = 0;

    /**
     * @var int
     */
    protected int $size = 3;

    /**
     * @return string
     */
    public function getAllowed(): string
    {
        return $this->allowed;
    }

    /**
     * @param string $allowed
     */
    public function setAllowed(string $allowed): void
    {
        $this->allowed = $allowed;
    }

    /**
     * @return string
     */
    public function getInternalType(): string
    {
        return $this->internalType;
    }

    /**
     * @param string $internalType
     */
    public function setInternalType(string $internalType): void
    {
        $this->internalType = $internalType;
    }

    /**
     * @return int
     */
    public function getMaxItems(): int
    {
        return $this->maxItems;
    }

    /**
     * @param int $maxItems
     */
    public function setMaxItems(int $maxItems): void
    {
        $this->maxItems = $maxItems;
    }

    /**
     * @return int
     */
    public function getMinItems(): int
    {
        return $this->minItems;
    }

    /**
     * @param int $minItems
     */
    public function setMinItems(int $minItems): void
    {
        $this->minItems = $minItems;
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
