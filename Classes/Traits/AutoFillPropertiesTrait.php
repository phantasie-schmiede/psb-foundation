<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Traits;

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

use PSB\PsbFoundation\Utility\StringUtility;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Trait AutoFillPropertiesTrait
 *
 * @package PSB\PsbFoundation\Traits
 */
trait AutoFillPropertiesTrait
{
    /**
     * @param array $properties
     *
     * @throws ReflectionException
     */
    public function fillProperties(array $properties): void
    {
        $reflectionClass = GeneralUtility::makeInstance(ReflectionClass::class, $this);

        foreach ($properties as $property => $value) {
            $property = StringUtility::sanitizePropertyName($property);
            $setterMethodName = 'set' . ucfirst($property);

            if ($reflectionClass->hasMethod($setterMethodName)) {
                $reflectionMethod = GeneralUtility::makeInstance(ReflectionMethod::class, $this, $setterMethodName);
                $reflectionMethod->invoke($this, $value);
            } elseif (!StringUtility::beginsWith($property, '@')) {
                // Show missing getter-methods.
//                \TYPO3\CMS\Core\Utility\DebugUtility::debug($property, get_class($this));
//                \TYPO3\CMS\Core\Utility\DebugUtility::debug($properties, get_class($this));
            }
        }
    }
}
