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
 * Class Group
 *
 * @Annotation
 * @package PSB\PsbFoundation\Service\DocComment\Annotations\TCA
 */
class Group extends AbstractTcaFieldAnnotation
{
    public const TYPE = Fields::FIELD_TYPES['GROUP'];

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
