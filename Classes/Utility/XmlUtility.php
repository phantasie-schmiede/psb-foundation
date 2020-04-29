<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019-2020 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * Class XmlUtility
 *
 * @package PSB\PsbFoundation\Utility
 */
class XmlUtility
{
    public const INDENTATION = '    ';

    public const SPECIAL_KEYS = [
        'ATTRIBUTES' => '@attributes',
        'NODE_VALUE' => '@nodeValue',
        'POSITION'   => '@position',
    ];

    /**
     * @param array  $array
     * @param string $path
     * @param bool   $strict
     *
     * @return mixed
     */
    public static function getNodeValue(array $array, string $path, bool $strict = true)
    {
        $path .= '.' . self::SPECIAL_KEYS['NODE_VALUE'];

        if (false === $strict && !ArrayUtility::isValidPath($array, $path, '.')) {
            return null;
        }

        return ArrayUtility::getValueByPath($array, $path, '.');
    }

    /**
     * @param array $array
     * @param bool  $wellFormatted
     * @param int   $indentationLevel
     *
     * @return string
     */
    public static function convertArrayToXml(
        array $array,
        bool $wellFormatted = true,
        int $indentationLevel = 0
    ): string {
        $xml = '';
        $siblings = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && !ArrayUtility::isAssociative($value)) {
                foreach ($value as $sibling) {
                    $siblings[] = [
                        'tagName'  => $key,
                        'tagValue' => $sibling,
                    ];
                }
            } else {
                $siblings[] = [
                    'tagName'  => $key,
                    'tagValue' => $value,
                ];
            }
        }

        $siblings = self::sortSiblings($siblings);

        foreach ($siblings as $value) {
            unset($value['tagValue'][self::SPECIAL_KEYS['POSITION']]);
            $xml .= self::buildTag($value['tagName'], $value['tagValue'], $wellFormatted, $indentationLevel);
        }

        return $xml;
    }

    /**
     * @param SimpleXMLElement|string $xml
     * @param bool                    $sortAlphabetically Sort tags on same level alphabetically by tag name.
     *
     * @return array
     */
    public static function convertXmlToArray($xml, bool $sortAlphabetically = false): array
    {
        if (is_string($xml)) {
            $xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_PARSEHUGE | LIBXML_NOCDATA);
        }

        if (!$xml instanceof SimpleXMLElement) {
            throw new RuntimeException(__CLASS__ . ': No valid XML provided!');
        }

        return self::buildArrayFromXml($sortAlphabetically, $xml);
    }

    /**
     * @param array  $array
     * @param string $path
     * @param mixed  $value
     */
    public static function setNodeValue(array &$array, string $path, $value): void
    {
        $path .= '.' . self::SPECIAL_KEYS['NODE_VALUE'];
        $array = ArrayUtility::setValueByPath($array, $path, $value, '.');
    }

    /**
     * @param bool                    $sortAlphabetically
     * @param SimpleXMLElement|string $xml
     * @param bool                    $rootLevel This is an internal parameter only to be set from within this function.
     *
     * @return array|string
     */
    private static function buildArrayFromXml(bool $sortAlphabetically, $xml, bool $rootLevel = true): array
    {
        if (is_string($xml)) {
            return $xml;
        }

        $array = [];

        foreach ($xml->attributes() as $attributeName => $value) {
            if ('version' !== $attributeName) {
                $value = StringUtility::convertString(trim((string)$value));
            }

            $array[self::SPECIAL_KEYS['ATTRIBUTES']][$attributeName] = $value;
        }

        $namespaces = $xml->getDocNamespaces();
        $namespaces[] = '';

        foreach ($namespaces as $prefix => $namespace) {
            if (0 === $prefix) {
                $prefix = '';
            } else {
                $prefix .= ':';
            }

            $positionOnThisLevel = 0;

            foreach ($xml->children($namespace) as $childTagName => $child) {
                $childTagName = $prefix . $childTagName;

                if (0 < $child->count()) {
                    $parsedChild = self::buildArrayFromXml($sortAlphabetically, $child, false);
                } else {
                    $parsedChild = self::parseTextNode($child);
                }

                $parsedChild[self::SPECIAL_KEYS['POSITION']] = $positionOnThisLevel++;

                if (!isset($array[$childTagName])) {
                    $array[$childTagName] = $parsedChild;
                } elseif (is_array($array[$childTagName]) && !ArrayUtility::isAssociative($array[$childTagName])) {
                    $array[$childTagName][] = $parsedChild;
                } else {
                    $array[$childTagName] = [
                        $array[$childTagName],
                        $parsedChild,
                    ];
                }
            }
        }

        if (true === $sortAlphabetically) {
            ksort($array);
        }

        if (true === $rootLevel) {
            return [$xml->getName() => $array];
        }

        return $array;
    }

    /**
     * @param string $key
     * @param        $value
     * @param bool   $wellFormatted
     * @param int    $indentationLevel
     *
     * @return string
     */
    private static function buildTag(string $key, $value, bool $wellFormatted, int $indentationLevel): string
    {
        $xml = '';

        if (true === $wellFormatted) {
            $indentation = self::createIndentation($indentationLevel);
            $linebreak = LF;
        } else {
            $indentation = '';
            $linebreak = '';
        }

        if (is_string($key)) {
            $xml .= $indentation . '<' . $key;

            if (is_array($value) && isset($value[self::SPECIAL_KEYS['ATTRIBUTES']]) && is_array($value[self::SPECIAL_KEYS['ATTRIBUTES']])) {
                foreach ($value[self::SPECIAL_KEYS['ATTRIBUTES']] as $attributeName => $attributeValue) {
                    $xml .= ' ' . $attributeName . '="' . $attributeValue . '"';
                }

                unset($value[self::SPECIAL_KEYS['ATTRIBUTES']]);
            }

            $xml .= '>';

            if (isset($value[self::SPECIAL_KEYS['NODE_VALUE']])) {
                $value = $value[self::SPECIAL_KEYS['NODE_VALUE']];
            }
        }

        if (is_array($value)) {
            $xml .= $linebreak . self::convertArrayToXml($value, $wellFormatted, ++$indentationLevel) . $indentation;
        } else {
            $xml .= $value;
        }

        if (is_string($key)) {
            if ('' === $value) {
                $xml = rtrim($xml, '>');
                $xml .= ' />' . $linebreak;
            } else {
                $xml .= '</' . $key . '>' . $linebreak;
            }
        }

        return $xml;
    }

    /**
     * @param int $indentationLevel
     *
     * @return string
     */
    private static function createIndentation(int $indentationLevel): string
    {
        $indentation = '';

        for ($i = 0; $i < $indentationLevel; $i++) {
            $indentation .= self::INDENTATION;
        }

        return $indentation;
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
                $parsedNode[self::SPECIAL_KEYS['ATTRIBUTES']][$attributeName] = StringUtility::convertString(trim((string)$value));
            }
        }

        $parsedNode[self::SPECIAL_KEYS['NODE_VALUE']] = StringUtility::convertString(trim((string)$node));

        return $parsedNode;
    }

    /**
     * @param array $siblings
     *
     * @return array
     */
    private static function sortSiblings(array $siblings): array
    {
        uasort($siblings, static function ($a, $b) {
            return $a['tagValue'][self::SPECIAL_KEYS['POSITION']] > $b['tagValue'][self::SPECIAL_KEYS['POSITION']];
        });

        return $siblings;
    }
}
