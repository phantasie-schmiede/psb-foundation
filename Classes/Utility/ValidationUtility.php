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

use InvalidArgumentException;

/**
 * Class ValidationUtility
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
}
