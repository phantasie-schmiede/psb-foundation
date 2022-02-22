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

namespace PSB\PsbFoundation\Annotation;

use Exception;
use PSB\PsbFoundation\Utility\ObjectUtility;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractAnnotation
 *
 * @package PSB\PsbFoundation\Annotation
 */
abstract class AbstractAnnotation
{
    /**
     * Maps associative arrays to object properties. Requires the class to have appropriate setter-methods.
     *
     * @param array $data
     *
     * @throws Exception
     */
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $reflectionClass = GeneralUtility::makeInstance(ReflectionClass::class, $this);

            foreach ($data as $propertyName => $propertyValue) {
                $setterMethodName = 'set' . GeneralUtility::underscoredToUpperCamelCase($propertyName);

                if ($reflectionClass->hasMethod($setterMethodName)) {
                    $reflectionMethod = GeneralUtility::makeInstance(ReflectionMethod::class, $this, $setterMethodName);
                    $reflectionMethod->invoke($this, $propertyValue);
                } else {
                    throw new RuntimeException(static::class . ': Class doesn\'t have a method named "' . $setterMethodName . '"!',
                        1610459852);
                }
            }
        }
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function toArray(): array
    {
        return ObjectUtility::toArray($this);
    }
}
