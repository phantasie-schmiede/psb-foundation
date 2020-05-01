<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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
     * @param string       $delimiter
     *
     * @return mixed
     */
    public static function getValueByPath($variable, string $path, string $delimiter = '.')
    {
        $pathSegments = GeneralUtility::trimExplode($delimiter, $path);
        $value = $variable;

        foreach ($pathSegments as $pathSegment) {
            if (is_array($value)) {
                $value = $value[$pathSegment];
            } elseif (is_object($value)) {
                $getterMethod = 'get' . ucfirst($pathSegment);
                $value = $value->$getterMethod();
            }
        }

        return $value;
    }
}
