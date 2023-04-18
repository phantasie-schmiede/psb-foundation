<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Utility\Xml;

use JsonException;
use PSB\PsbFoundation\Utility\StringUtility;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use SimpleXMLElement;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use function count;
use function is_array;
use function is_string;

/**
 * Class XmlUtility
 *
 * @package PSB\PsbFoundation\Utility\Xml
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
     * @param SimpleXMLElement|string $xml
     * @param bool                    $sortAlphabetically Sort tags on same level alphabetically by tag name.
     * @param array                   $mapping
     *
     * @return object|array|string
     * @throws ContainerExceptionInterface
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public static function convertFromXml(SimpleXMLElement|string $xml, bool $sortAlphabetically = false, array $mapping = []): object|array|string
    {
        if (is_string($xml)) {
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_PARSEHUGE | LIBXML_NOCDATA);

            if (!$xml instanceof SimpleXMLElement) {
                throw new RuntimeException(__CLASS__ . ': No valid XML provided!');
            }
        }

        return self::buildFromXml($sortAlphabetically, $xml, $mapping);
    }

    /**
     * @param array|XmlElementInterface $data
     * @param bool                      $wellFormatted
     * @param int                       $indentationLevel
     *
     * @return string
     */
    public static function convertToXml(
        array|XmlElementInterface $data,
        bool $wellFormatted = true,
        int $indentationLevel = 0
    ): string {
        $xml = '';
        $siblings = [];

        if ($data instanceof XmlElementInterface) {
            $data = [$data::getTagName() => $data->toArray()];
        }

        foreach ($data as $key => $value) {
            if (false === $value) {
                continue;
            }

            if ($value instanceof XmlElementInterface) {
                $value = $value->toArray();
            }

            if (is_array($value) && !ArrayUtility::isAssociative($value)) {
                foreach ($value as $sibling) {
                    if ($sibling instanceof XmlElementInterface) {
                        $sibling = $sibling->toArray();
                    }

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
            if (isset($value['tagValue'][self::SPECIAL_KEYS['POSITION']])) {
                unset($value['tagValue'][self::SPECIAL_KEYS['POSITION']]);
            }

            $xml .= self::buildTag($value['tagName'], $value['tagValue'], $wellFormatted, $indentationLevel);
        }

        return $xml;
    }

    /**
     * @param array  $array
     * @param string $path
     * @param bool   $strict
     *
     * @return mixed
     */
    public static function getNodeValue(array $array, string $path, bool $strict = true): mixed
    {
        $path .= '.' . self::SPECIAL_KEYS['NODE_VALUE'];

        if (false === $strict && !ArrayUtility::isValidPath($array, $path, '.')) {
            return null;
        }

        return ArrayUtility::getValueByPath($array, $path, '.');
    }

    /**
     * @param array  $array
     * @param string $path
     * @param bool   $strict
     *
     * @return void
     */
    public static function removeNode(array &$array, string $path, bool $strict = false): void
    {
        if (false === $strict && !ArrayUtility::isValidPath($array, $path, '.')) {
            return;
        }

        $array = ArrayUtility::removeByPath($array, $path, '.');
    }

    /**
     * @param string $tagName
     *
     * @return string
     */
    public static function sanitizeTagName(string $tagName): string
    {
        return str_replace('_', '-', GeneralUtility::camelCaseToLowerCaseUnderscored($tagName));
    }

    /**
     * @param array  $array
     * @param string $path
     * @param mixed  $value
     */
    public static function setNodeValue(array &$array, string $path, mixed $value): void
    {
        $path .= '.' . self::SPECIAL_KEYS['NODE_VALUE'];
        $array = ArrayUtility::setValueByPath($array, $path, $value, '.');
    }

    /**
     * @param bool                    $sortAlphabetically
     * @param SimpleXMLElement|string $xml
     * @param array                   $mapping
     * @param bool                    $rootLevel This is an internal parameter only to be set from within this function.
     *
     * @return array|object|string
     * @throws ContainerExceptionInterface
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    private static function buildFromXml(bool $sortAlphabetically, SimpleXMLElement|string $xml, array $mapping, bool $rootLevel = true): object|array|string
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

        $namespaces = $xml->getDocNamespaces() ?: [];
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
                    $parsedChild = self::buildFromXml($sortAlphabetically, $child, $mapping, false);
                } else {
                    $parsedChild = self::parseTextNode($child);
                }

                if (isset($mapping[$childTagName])) {
                    $parsedChild = GeneralUtility::makeInstance($mapping[$childTagName], $parsedChild);
                }

                if ($parsedChild instanceof XmlElementInterface) {
                    $parsedChild->_setPosition($positionOnThisLevel++);
                } else {
                    $parsedChild[self::SPECIAL_KEYS['POSITION']] = $positionOnThisLevel++;
                }

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
            $rootName = $xml->getName();

            if (isset($mapping[$rootName])) {
                return GeneralUtility::makeInstance($mapping[$rootName], $array);
            }

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

        $xml .= $indentation . '<' . $key;

        if (is_array($value) && isset($value[self::SPECIAL_KEYS['ATTRIBUTES']]) && is_array($value[self::SPECIAL_KEYS['ATTRIBUTES']])) {
            foreach ($value[self::SPECIAL_KEYS['ATTRIBUTES']] as $attributeName => $attributeValue) {
                $xml .= ' ' . $attributeName . '="' . $attributeValue . '"';
            }

            unset($value[self::SPECIAL_KEYS['ATTRIBUTES']]);
        }

        $xml .= '>';

        if (is_array($value) && isset($value[self::SPECIAL_KEYS['NODE_VALUE']])) {
            $value = $value[self::SPECIAL_KEYS['NODE_VALUE']];
        }

        if (is_array($value) || $value instanceof XmlElementInterface) {
            $xml .= $linebreak . self::convertToXml($value, $wellFormatted, ++$indentationLevel) . $indentation;
        } else {
            $xml .= $value;
        }

        if ('' === $value) {
            $xml = rtrim($xml, '>');
            $xml .= ' />' . $linebreak;
        } else {
            $xml .= '</' . $key . '>' . $linebreak;
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
        return str_repeat(self::INDENTATION, $indentationLevel);
    }

    /**
     * @param SimpleXMLElement $node
     *
     * @return array
     * @throws ContainerExceptionInterface
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    private static function parseTextNode(SimpleXMLElement $node): array
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
            $positionA = 0;
            $positionB = 0;

            if ($a['tagValue'] instanceof XmlElementInterface) {
                $positionA = $a['tagValue']->_getPosition();
            } elseif (is_array($a['tagValue'])) {
                $positionA = $a['tagValue'][self::SPECIAL_KEYS['POSITION']] ?? 0;
            }

            if ($b['tagValue'] instanceof XmlElementInterface) {
                $positionB = $b['tagValue']->_getPosition();
            } elseif (is_array($b['tagValue'])) {
                $positionB = $b['tagValue'][self::SPECIAL_KEYS['POSITION']] ?? 0;
            }

            return $positionA > $positionB;
        });

        return $siblings;
    }
}
