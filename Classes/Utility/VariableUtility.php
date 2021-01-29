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
