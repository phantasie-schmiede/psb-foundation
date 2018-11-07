<?php
declare(strict_types=1);

namespace PS\PsFoundation\Utilities;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018 Daniel Ablass <dn@phantasie-schmiede.de>, Phantasie-Schmiede
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
 * Class VariableCastUtility
 * @package PS\PsFoundation\Utilities
 */
class VariableCastUtility
{
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
     * Convert file size to a human readable string
     *
     * To enforce a specific unit use a value of FILE_SIZE_UNITS as second parameter
     *
     * @param int $bytes
     * @param int|null $unit
     * @param int $decimals
     * @param string $decimalSeparator
     * @param string $thousandsSeparator
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

        return number_format($bytes, $decimals, $decimalSeparator, $thousandsSeparator).' '.$unitString;
    }

    /**
     * @param string $variable
     *
     * @return bool|float|int|string
     */
    public static function convertString(string $variable)
    {
        if (is_numeric($variable)) {
            if (false === strpos($variable, '.')) {
                $output = (int)$variable;
            } else {
                $output = (double)$variable;
            }
        } else {
            switch ($variable) {
                case 'true':
                    $output = true;
                    break;
                case 'false':
                    $output = false;
                    break;
                default:
                    $output = $variable;
            }
        }

        return $output;
    }
}
