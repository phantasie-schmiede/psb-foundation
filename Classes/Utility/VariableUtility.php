<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Utility;

use RuntimeException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class VariableUtility
 *
 * @package PSB\PsbFoundation\Utility
 */
class VariableUtility
{
    /**
     * @param array|object $variable
     * @param string       $path
     * @param bool         $strict If set to false this function will return null if path does not exist.
     * @param string       $delimiter
     *
     * @return mixed
     */
    public static function getValueByPath($variable, string $path, bool $strict = true, string $delimiter = '.')
    {
        $pathSegments = GeneralUtility::trimExplode($delimiter, $path);
        $value = $variable;

        foreach ($pathSegments as $pathSegment) {
            if (is_array($value) && array_key_exists($pathSegment, $value)) {
                $value = $value[$pathSegment];
            } elseif (is_object($value)) {
                $getterMethod = 'get' . ucfirst($pathSegment);
                $value = $value->$getterMethod();
            } else {
                if (false === $strict) {
                    return null;
                }

                throw new RuntimeException(__CLASS__ . ': Path "' . $path . '" does not exist in array or object!',
                    1614066725);
            }
        }

        return $value;
    }
}
