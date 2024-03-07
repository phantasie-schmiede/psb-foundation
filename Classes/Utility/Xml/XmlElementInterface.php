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
    public static function getTagName(): string;

    public function _getAttributes(): array;

    public function _getNodeValue(): mixed;

    public function _getPosition(): ?int;

    public function _setAttributes(array $attributes): void;

    public function _setNodeValue(mixed $nodeValue): void;

    public function _setPosition(int $position): void;

    public function toArray(): array;
}
