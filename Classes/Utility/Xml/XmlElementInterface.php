<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
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
