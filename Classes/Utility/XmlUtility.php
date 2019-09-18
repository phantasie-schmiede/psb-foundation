<?php
declare(strict_types=1);

namespace PSB\PsbFoundation\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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

use RuntimeException;
use SimpleXMLElement;

/**
 * Class XmlUtility
 * @package PSB\PsbFoundation\Utility
 */
class XmlUtility
{
    public const ATTRIBUTES_KEY = '@attributes';
    public const NODE_VALUE_KEY = '@nodeValue';

    /**
     * @param SimpleXMLElement|string $xml
     * @param bool                    $rootLevel
     *
     * @return array|string
     */
    public static function buildArrayFromXml($xml, bool $rootLevel = true): array
    {
        if (is_string($xml)) {
            return $xml;
        }

        $array = [];

        foreach ($xml->attributes() as $attributeName => $value) {
            $array[self::ATTRIBUTES_KEY][$attributeName] = StringUtility::convertString(trim((string)$value));
        }

        $namespaces = $xml->getDocNamespaces();
        $namespaces[] = '';

        foreach ($namespaces as $prefix => $namespace) {
            if (0 === $prefix) {
                $prefix = '';
            } else {
                $prefix .= ':';
            }

            foreach ($xml->children($namespace) as $childTagName => $child) {
                $childTagName = $prefix . $childTagName;

                if (0 < $child->count()) {
                    $parsedChild = self::buildArrayFromXml($child, false);
                } else {
                    $parsedChild = self::parseTextNode($child);
                }

                if (!isset($array[$childTagName])) {
                    $array[$childTagName] = $parsedChild;
                } elseif (is_array($array[$childTagName]) && ArrayUtility::isNumericArray($array[$childTagName])) {
                    $array[$childTagName][] = $parsedChild;
                } else {
                    $array[$childTagName] = [
                        $array[$childTagName],
                        $parsedChild,
                    ];
                }
            }
        }

        ksort($array);

        if (true === $rootLevel) {
            return [$xml->getName() => $array];
        }

        return $array;
    }

    /**
     * @param array $array
     *
     * @return string
     */
    public static function convertArrayToXml(array $array): string
    {
        $xml = '';

        foreach ($array as $key => $value) {
            if (is_string($key)) {
                $xml .= '<' . $key;

                if (is_array($value) && isset($value[self::ATTRIBUTES_KEY]) && is_array($value[self::ATTRIBUTES_KEY])) {
                    foreach ($value[self::ATTRIBUTES_KEY] as $attributeName => $attributeValue) {
                        $xml .= ' ' . $attributeName . '="' . $attributeValue . '"';
                    }

                    unset($value[self::ATTRIBUTES_KEY]);
                }

                $xml .= '>';
            }

            if (is_array($value)) {
                $xml .= self::convertArrayToXml($value);
            } else {
                $xml .= $value;
            }

            if (is_string($key)) {
                $xml .= '</' . $key . '>';
            }
        }

        return $xml;
    }

    /**
     * @param SimpleXMLElement|string $xml
     *
     * @return array
     */
    public static function convertXmlToArray($xml): array
    {
        if (is_string($xml)) {
            $xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_PARSEHUGE | LIBXML_NOCDATA);
        }

        if (!$xml instanceof SimpleXMLElement) {
            throw new RuntimeException(__CLASS__ . ': No valid XML provided!');
        }

        return self::buildArrayFromXml($xml);
    }

    /**
     * @param SimpleXMLElement $node
     *
     * @return array|bool|float|int|string|null
     */
    private static function parseTextNode(SimpleXMLElement $node)
    {
        if (count($node->attributes())) {
            foreach ($node->attributes() as $attributeName => $value) {
                $parsedNode[self::ATTRIBUTES_KEY][$attributeName] = StringUtility::convertString(trim((string)$value));
            }

            $parsedNode[self::NODE_VALUE_KEY] = StringUtility::convertString(trim((string)$node));
        } else {
            $parsedNode = StringUtility::convertString(trim((string)$node));
        }

        return $parsedNode;
    }
}
