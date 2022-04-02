<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
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
     * @return void
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
