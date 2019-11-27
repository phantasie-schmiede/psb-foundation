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

/**
 * Class ArrayUtility
 * @package PSB\PsbFoundation\Utility
 */
class ArrayUtility
{
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
        if (!self::isAssociativeArray($array)) {
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
    public static function isAssociativeArray(array $array): bool
    {
        return 0 === count(array_filter($array, 'is_numeric', ARRAY_FILTER_USE_KEY));
    }

    /**
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

        if (self::isAssociativeArray($array)) {
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
