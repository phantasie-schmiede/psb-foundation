<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Utility;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ObjectUtility
 *
 * @package PSB\PsbFoundation\Utility
 */
class ObjectUtility
{
    public const NAMESPACE_FALLBACK_KEY = '__fallback';

    /**
     * @param string $className
     * @param array  $namespaces
     *
     * @return bool|string
     */
    public static function getFullQualifiedClassName(string $className, array $namespaces)
    {
        if (class_exists($className)) {
            return $className;
        }

        [$alias, $appendix] = GeneralUtility::trimExplode('\\', $className, true, 2);

        if (isset($namespaces[$alias])) {
            return $namespaces[$alias] . ($appendix ? ('\\' . $appendix) : '');
        }

        if (isset($namespaces[self::NAMESPACE_FALLBACK_KEY])) {
            $fallbackClassName = $namespaces[self::NAMESPACE_FALLBACK_KEY] . '\\' . $className;

            if (class_exists($fallbackClassName)) {
                return $fallbackClassName;
            }
        }

        return false;
    }

    /**
     * @param object $object
     *
     * @return array
     * @throws ReflectionException
     */
    public static function toArray(object $object): array
    {
        $arrayRepresentation = [];
        $reflectionClass = GeneralUtility::makeInstance(ReflectionClass::class, $object);
        $properties = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $getterMethodName = 'get' . GeneralUtility::underscoredToUpperCamelCase($property->getName());

            if (!$reflectionClass->hasMethod($getterMethodName)) {
                $getterMethodName = 'is' . GeneralUtility::underscoredToUpperCamelCase($property->getName());
            }

            if ($reflectionClass->hasMethod($getterMethodName)) {
                $reflectionMethod = GeneralUtility::makeInstance(ReflectionMethod::class, $object, $getterMethodName);
                $value = $reflectionMethod->invoke($object);

                if (null !== $value) {
                    $arrayRepresentation[$property->getName()] = $value;
                }
            }
        }

        return $arrayRepresentation;
    }
}
