<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
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
                    /*
                     * Remove comment artifacts if string value stretches across multiple lines.
                     * Pattern: - linebreak
                     *          - followed by any number of whitespaces
                     *          - followed by *
                     *          - followed by any number of whitespaces
                     */
                    $propertyValue = preg_replace('/(\r\n|\r|\n)\s*\*\s*/', ' ', $propertyValue);

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
