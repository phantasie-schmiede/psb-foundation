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
 * Class Group
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA\Column
 */
class Group extends AbstractColumnAnnotation
{
    public const TYPE = self::TYPES['GROUP'];

    /**
     * @var string
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Group/Properties/Allowed.html
     */
    protected string $allowed = '*';

    /**
     * @var string
     */
    protected string $internalType = 'db';

    /**
     * @var int
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/CommonProperties/Maxitems.html#tca-property-maxitems
     */
    protected int $maxItems = 1;

    /**
     * @var int
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/CommonProperties/Minitems.html#tca-property-minitems
     */
    protected int $minItems = 0;

    /**
     * @var int
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/CommonProperties/Size.html#tca-property-size
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
