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

namespace PSB\PsbFoundation\Utility;

use TYPO3\CMS\Core\Utility\ArrayUtility as Typo3ArrayUtility;
use function array_slice;
use function is_array;
use function is_string;

/**
 * Class ArrayUtility
 *
 * @package PSB\PsbFoundation\Utility
 */
class ArrayUtility
{
    /**
     * @param array $array
     *
     * @return int
     */
    public static function countRecursive(array $array): int
    {
        $count = 0;

        foreach ($array as $element) {
            if (is_array($element)) {
                $count += self::countRecursive($element);
            } else {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param $variable
     *
     * @return array
     */
    public static function guaranteeArrayType($variable): array
    {
        if (!is_array($variable)) {
            $variable = [$variable];
        }

        return $variable;
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

                if (false !== $result) {
                    return $returnIndex ? ($key . '.' . $result) : true;
                }
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
        if (!Typo3ArrayUtility::isAssociative($array)) {
            $combinedArray = [];
            array_push($combinedArray, ...array_slice($array, 0, $index), ...$elements,
                ...array_slice($array, $index));

            return $combinedArray;
        }

        return array_slice($array, 0, $index, true) + $elements + array_slice($array, $index, null, true);
    }

    /**
     * This function shuffles associative arrays and those with integer keys - even multidimensional ones if desired.
     *
     * @param array $array
     * @param bool  $recursive
     */
    public static function shuffle(array &$array, bool $recursive = false): void
    {
        if ($recursive) {
            foreach ($array as &$item) {
                if (is_array($item)) {
                    self::shuffle($item, true);
                }
            }
        }

        unset($item);

        if (Typo3ArrayUtility::isAssociative($array)) {
            $shuffledArray = [];
            $keys = array_keys($array);
            shuffle($keys);

            foreach ($keys as $key) {
                $shuffledArray[$key] = $array[$key];
            }

            $array = $shuffledArray;
        } else {
            shuffle($array);
        }
    }
}
