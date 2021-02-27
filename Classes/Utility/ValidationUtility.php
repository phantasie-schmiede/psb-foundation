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

use InvalidArgumentException;

/**
 * Class ValidationUtility
 *
 * @package PSB\PsbFoundation\Utility
 */
class ValidationUtility
{
    /**
     * @param array $constant
     * @param array $keys
     */
    public static function checkArrayAgainstConstantKeys(array $constant, array $keys): void
    {
        foreach ($keys as $key) {
            self::checkKeyAgainstConstant($constant, $key);
        }
    }

    /**
     * @param array $constant
     * @param array $values
     */
    public static function checkArrayAgainstConstantValues(array $constant, array $values): void
    {
        foreach ($values as $value) {
            self::checkValueAgainstConstant($constant, $value);
        }
    }

    /**
     * @param array  $constant
     * @param string $key
     */
    public static function checkKeyAgainstConstant(array $constant, string $key): void
    {
        if (!isset($constant[$key])) {
            throw new InvalidArgumentException(self::class . ': Key "' . $key . '" is not present in constant!',
                1564122378);
        }
    }

    /**
     * @param array $constant
     * @param       $value
     */
    public static function checkValueAgainstConstant(array $constant, $value): void
    {
        if (!in_array($value, $constant, true)) {
            throw new InvalidArgumentException(self::class . ': Value "' . $value . '" is not present in constant!',
                1564068237);
        }
    }
}
