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

namespace PSB\PsbFoundation\Utility\Xml;

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
