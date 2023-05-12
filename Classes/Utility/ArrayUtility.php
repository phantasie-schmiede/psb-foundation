<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
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
     * @param mixed $needle
     * @param array $array
     *
     * @return false|int|string
     */
    public static function findLastOccurrence(mixed $needle, array $array): bool|int|string
    {
        return array_search($needle, array_reverse($array, true), true);
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
     *
     *
     * @param array $haystack
     * @param mixed $needle
     * @param bool  $searchForSubstring
     *
     * @return array
     */
    public static function inArrayRecursive(
        array $haystack,
        mixed $needle,
        bool  $searchForSubstring = false
    ): array {
        $results = [];

        foreach ($haystack as $key => $value) {
            if ($value === $needle || (true === $searchForSubstring && is_string($value) && str_contains($value,
                        $needle))) {
                $results[] = $key;
            }

            if (is_array($value)) {
                $subResult = self::inArrayRecursive($value, $needle, $searchForSubstring);

                if (!empty($subResult)) {
                    foreach ($subResult as $subKey) {
                        $results[] = $key . '.' . $subKey;
                    }
                }
            }
        }

        return $results;
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
            array_push($combinedArray, ...array_slice($array, 0, $index), ...$elements, ...array_slice($array, $index));

            return $combinedArray;
        }

        return array_slice($array, 0, $index, true) + $elements + array_slice($array, $index, null, true);
    }

    /**
     * This function shuffles associative arrays and those with integer keys - even multidimensional ones if desired.
     *
     * @param array $array
     * @param bool  $recursive
     *
     * @return void
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
