<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Utility\Xml;

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

/**
 * Interface XmlElementInterface
 *
 * @package PSB\PsbFoundation\Utility\Xml
 */
interface XmlElementInterface
{
    /**
     * @return string
     */
    public static function getTagName(): string;

    /**
     * @return array
     */
    public function _getAttributes(): array;

    /**
     * @return mixed
     */
    public function _getNodeValue();

    /**
     * @return int|null
     */
    public function _getPosition(): ?int;

    /**
     * @param array $attributes
     */
    public function _setAttributes(array $attributes): void;

    /**
     * @param mixed $nodeValue
     */
    public function _setNodeValue($nodeValue): void;

    /**
     * @param int $position
     */
    public function _setPosition(int $position): void;

    /**
     * @return array
     */
    public function toArray(): array;
}
