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

namespace PSB\PsbFoundation\Traits;

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
            }
        }
    }
}
