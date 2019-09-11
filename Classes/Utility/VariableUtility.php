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

use Exception;
use InvalidArgumentException;
use PSB\PsbFoundation\Service\DocComment\DocCommentParserService;
use PSB\PsbFoundation\Service\DocComment\ValueParsers\TcaMappingParser;
use PSB\PsbFoundation\Traits\StaticInjectionTrait;
use ReflectionException;
use RuntimeException;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class VariableUtility
 * @package PSB\PsbFoundation\Utility
 */
class VariableUtility
{
    use StaticInjectionTrait;

    /**
     * Although calculated on a base of 2, the average user might be confused when he is shown the technically correct
     * unit names like KiB, MiB or GiB. Hence the inaccurate, "old" units are being used.
     */
    public const FILE_SIZE_UNITS = [
        'B'  => 0,
        'KB' => 1,
        'MB' => 2,
        'GB' => 3,
        'TB' => 4,
        'PB' => 5,
        'EB' => 6,
        'ZB' => 7,
        'YB' => 8,
    ];

    /**
     * @param array  $constant
     * @param string $key
     */
    public static function checkKeyAgainstConstant(array $constant, string $key): void
    {
        if (!isset($constant[$key])) {
            throw new InvalidArgumentException(self::class . ': Key "' . $key . '" is not present in constant. Possible keys: ' . implode(', ',
                    array_keys($constant)), 1564122378);
        }
    }

    /**
     * @param array $constant
     * @param       $value
     */
    public static function checkValueAgainstConstant(array $constant, $value): void
    {
        if (!in_array($value, $constant, true)) {
            throw new InvalidArgumentException(self::class . ': Value "' . $value . '" is not present in constant. Possible values: ' . implode(', ',
                    $constant), 1564068237);
        }
    }

    /**
     * @param $url
     *
     * @return string
     */
    public static function cleanUrl($url): string
    {
        return html_entity_decode(urldecode($url));
    }

    /**
     * @TODO: check if necessary
     *
     * @param string $className
     *
     * @return string The controller name for Extbase-configurations (without the 'Controller'-part)
     */
    public static function convertClassNameToControllerName(string $className): string
    {
        $classNameParts = GeneralUtility::trimExplode('\\', $className, true);

        if (4 > count($classNameParts)) {
            throw new InvalidArgumentException(__CLASS__ . ': ' . $className . ' is not a full qualified (namespaced) class name!',
                1560233275);
        }

        $controllerNameParts = array_slice($classNameParts, 3);
        $fullControllerName = implode('\\', $controllerNameParts);

        if (!self::endsWith($fullControllerName, 'Controller')) {
            throw new InvalidArgumentException(__CLASS__ . ': ' . $className . ' is not a controller class!',
                1560233166);
        }

        return substr($fullControllerName, 0, -10);
    }

    /**
     * @param string $className
     *
     * @return string
     */
    public static function convertClassNameToExtensionKey(string $className): string
    {
        $classNameParts = GeneralUtility::trimExplode('\\', $className, true);

        if (isset($classNameParts[1])) {
            return GeneralUtility::camelCaseToLowerCaseUnderscored($classNameParts[1]);
        }

        throw new InvalidArgumentException(__CLASS__ . ': ' . $className . ' is not a full qualified (namespaced) class name!',
            1547120513);
    }

    /**
     * @param string $className
     *
     * @return string
     * @throws ReflectionException
     * @throws NoSuchCacheException
     */
    public static function convertClassNameToTableName(string $className): string
    {
        $docCommentParserService = self::get(DocCommentParserService::class);
        $docComment = $docCommentParserService->parsePhpDocComment($className);

        if (isset($docComment[TcaMappingParser::ANNOTATION_TYPE]['table'])) {
            return $docComment[TcaMappingParser::ANNOTATION_TYPE]['table'];
        }

        $classNameParts = GeneralUtility::trimExplode('\\', $className, true);
        $classNameParts[0] = 'tx';

        return strtolower(implode('_', $classNameParts));
    }

    /**
     * Convert file size to a human readable string
     *
     * To enforce a specific unit use a value of FILE_SIZE_UNITS as second parameter
     *
     * @param int      $bytes
     * @param int|null $unit
     * @param int      $decimals
     * @param string   $decimalSeparator
     * @param string   $thousandsSeparator
     *
     * @return string
     */
    public static function convertFileSize(
        int $bytes,
        int $unit = null,
        int $decimals = 2,
        $decimalSeparator = ',',
        $thousandsSeparator = '.'
    ): string {
        if ($unit) {
            $bytes /= (1024 ** $unit);
        } else {
            $power = 0;

            while ($bytes >= 1024) {
                $bytes /= 1024;
                $power++;
            }
        }

        $unitString = array_search($power ?? $unit, self::FILE_SIZE_UNITS, true);

        return number_format($bytes, $decimals, $decimalSeparator, $thousandsSeparator) . ' ' . $unitString;
    }

    /**
     * @param string      $propertyName
     * @param string|null $className
     *
     * @return string
     * @throws NoSuchCacheException
     * @throws ReflectionException
     */
    public static function convertPropertyNameToColumnName(string $propertyName, string $className = null): string
    {
        if (null !== $className) {
            $docCommentParserService = self::get(DocCommentParserService::class);
            $docComment = $docCommentParserService->parsePhpDocComment($className, $propertyName);
        }

        return $docComment[TcaMappingParser::ANNOTATION_TYPE]['column'] ?? GeneralUtility::camelCaseToLowerCaseUnderscored($propertyName);
    }

    /**
     * @param string|null $variable
     * @param bool        $convertEmptyStringToNull
     *
     * @return mixed
     */
    public static function convertString(?string $variable, $convertEmptyStringToNull = false)
    {
        if (null === $variable || ($convertEmptyStringToNull && '' === $variable)) {
            return null;
        }

        if (is_numeric($variable)) {
            if (false === strpos($variable, '.')) {
                return (int)$variable;
            }

            return (double)$variable;
        }

        if ('' !== $variable && '\\' === $variable[0] && 0 < strpos($variable, '::')) {
            if (0 < preg_match_all('/\[\'?(.*)\'?(\](?=[\[])|\]$)/U', $variable, $matches)) {
                $matches = array_map(static function ($value) {
                    return self::convertString(trim($value, '\''));
                }, $matches[1]);
                $variable = constant(preg_replace('/\[\'?(.*)\'?\]/', '', $variable));

                try {
                    return ArrayUtility::getValueByPath($variable, $matches);
                } catch (Exception $e) {
                    throw new RuntimeException('Path "' . implode('->', $matches) . '" does not exist in array!',
                        1548170593);
                }
            }

            return constant($variable);
        }

        if ('' !== $variable && in_array($variable[0], ['{', '['], true)) {
            $decodedString = json_decode(str_replace('\'', '"', $variable), true);

            if (null !== $decodedString) {
                return $decodedString;
            }
        }

        switch ($variable) {
            case 'true':
                return true;
                break;
            case 'false':
                return false;
                break;
            default:
                return $variable;
        }
    }

    /**
     * @param string $data
     *
     * @return string
     */
    public static function createHash(string $data): string
    {
        return hash('sha256', $data);
    }

    /**
     * @param string $string
     * @param int    $length
     * @param string $appendix
     * @param bool   $respectWordBoundaries
     * @param bool   $respectHtml Increases length of output string until all opened tags are properly closed
     *
     * @return string
     */
    public static function crop(
        string $string,
        int $length,
        string $appendix = '…',
        bool $respectWordBoundaries = true,
        bool $respectHtml = true
    ): string {
        if (mb_strlen($string) <= $length) {
            return $string;
        }

        $lastCharacterBeforeTruncation = '';

        if (true === $respectHtml) {
            $preparedString = preg_replace_callback('/<.*>/U', static function ($matches) {
                return '###TAG###' . $matches[0] . '###TAG###';
            }, $string);

            $stringParts = array_filter(explode('###TAG###', $preparedString));
            $openedTags = [];
            $pureTextLength = 0;
            $outputString = '';

            foreach ($stringParts as $stringPart) {
                if ('/>' !== mb_substr($stringPart, -2)) {
                    if (0 === mb_strpos($stringPart, '</')) {
                        $lastOpenedTag = array_pop($openedTags);
                        preg_match('/<\/(.+)>/U', $stringPart, $matches);
                        $closedTag = $matches[1];

                        if ($lastOpenedTag !== $closedTag) {
                            throw new RuntimeException(__CLASS__ . ': HTML tags in the input string are not properly nested.',
                                1565696694);
                        }
                    } elseif (0 === mb_strpos($stringPart, '<')) {
                        // extract the tag name
                        preg_match('/<(.+)[\s>]/U', $stringPart, $matches);
                        $openedTags[] = $matches[1];
                    } else {
                        if (empty($openedTags)) {
                            $stringPart = mb_substr($stringPart, 0, $length - $pureTextLength);
                        }

                        $lastCharacterBeforeTruncation = mb_substr($stringPart, -1);
                        $pureTextLength += mb_strlen($stringPart);
                    }
                }

                $outputString .= $stringPart;

                if (empty($openedTags) && $pureTextLength >= $length) {
                    $length = mb_strlen($outputString);
                    break;
                }
            }
        }

        if (true === $respectWordBoundaries) {
            $notMultiByteLength = strlen(mb_substr($string, 0, $length));
            preg_match('/[\n|\s]/', $string, $matches, 0, $notMultiByteLength);

            if (!empty($matches)) {
                $length = mb_strpos($string, $matches[0], $length);
            }

            $lastCharacterBeforeTruncation = mb_substr($string, $length - 1, 1);
        }

        if (in_array($lastCharacterBeforeTruncation, ['.', '!', '?'], true)) {
            $appendix = '';
        }

        return mb_substr($string, 0, $length) . $appendix;
    }

    /**
     * @TODO: check if necessary
     *
     * @param string $string
     * @param string $ending
     *
     * @return bool
     */
    public static function endsWith(string $string, string $ending): bool
    {
        $offset = strlen($ending);

        if ($offset > strlen($string)) {
            return false;
        }

        return strpos($string, $ending, -$offset) === strlen($string) - $offset;
    }

    /**
     * @param array $haystack
     * @param mixed $needle
     * @param bool  $returnIndex
     * @param bool  $searchForSubstring
     *
     * @return bool|int
     */
    public static function inArrayRecursive(
        array $haystack,
        $needle,
        bool $returnIndex = false,
        bool $searchForSubstring = false
    ) {
        foreach ($haystack as $key => $value) {
            if ($value === $needle || (true === $searchForSubstring && is_string($value) && false !== strpos($value,
                        $needle))) {
                return $returnIndex ? $key : true;
            }

            if (is_array($value)) {
                $result = self::inArrayRecursive($value, $needle, $returnIndex, $searchForSubstring);

                return ($result && $returnIndex) ? $key : $result;
            }
        }

        return false;
    }

    /**
     * @param array $array
     * @param array $elements
     * @param int   $index
     *
     * @return array
     */
    public static function insertIntoArray(array $array, array $elements, int $index): array
    {
        if (self::isNumericArray($array)) {
            $combinedArray = [];
            array_push($combinedArray, ...array_slice($array, 0, $index), ...$elements,
                ...array_slice($array, $index));

            return $combinedArray;
        }

        /** @noinspection AdditionOperationOnArraysInspection */
        return array_slice($array, 0, $index, true) + $elements + array_slice($array, $index, null, true);
    }

    /**
     * @param array $array
     *
     * @return bool
     */
    public static function isNumericArray(array $array): bool
    {
        return 0 < count(array_filter($array, 'is_numeric', ARRAY_FILTER_USE_KEY));
    }

    /**
     * @TODO: check if necessary
     *
     * @param string $string
     * @param string $beginning
     *
     * @return bool
     */
    public static function startsWith(string $string, string $beginning): bool
    {
        return 0 === strrpos($string, $beginning, -strlen($string));
    }
}
