<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019-2020 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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

use PSB\PsbFoundation\Exceptions\AnnotationException;
use PSB\PsbFoundation\Service\DocComment\Annotations\TCA\Mm;
use PSB\PsbFoundation\Service\DocComment\DocCommentParserService;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException;

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
     * @param array  $arguments
     *
     * @return object
     * @throws Exception
     */
    public static function get(string $className, ...$arguments): object
    {
        if (GeneralUtility::getContainer()->get('boot.state')->done) {
            return GeneralUtility::makeInstance(ObjectManager::class)->get($className, ...$arguments);
        }

        return GeneralUtility::makeInstance($className, ...$arguments);
    }

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
     * @param AbstractDomainObject $object
     * @param string               $property
     *
     * @return array
     * @throws AnnotationException
     * @throws Exception
     * @throws InvalidArgumentForHashGenerationException
     * @throws ReflectionException
     */
    public static function resolveMultipleMmRelation(AbstractDomainObject $object, string $property): array
    {
        $docCommentParser = self::get(DocCommentParserService::class);
        $docComment = $docCommentParser->parsePhpDocComment($object, $property);

        if (!isset($docComment[Mm::class])) {
            throw new RuntimeException(__CLASS__ . ': The property "' . $property . '" of object "' . get_class($object) . '" is not of TCA type mm!',
                1584867595);
        }

        // Store each ObjectStorage element by uid.
        $reflectionClass = GeneralUtility::makeInstance(\ReflectionClass::class, $object);
        $reflectionProperty = $reflectionClass->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $objectStorageElements = $reflectionProperty->getValue($object);
        $objectStorageElementsByUid = [];

        /** @var AbstractDomainObject $element */
        foreach ($objectStorageElements as $element) {
            $objectStorageElementsByUid[$element->getUid()] = $element;
        }

        // Get all mm-relation entries.
        /** @var Mm $mm */
        $mm = $docComment[Mm::class];
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($mm->getMm());
        $statement = $queryBuilder
            ->select('uid_foreign')
            ->from($mm->getMm())
            ->where(
                $queryBuilder->expr()
                    ->eq('uid_local', $queryBuilder->createNamedParameter($object->getUid()))
            )
            ->orderBy('sorting')
            ->execute();

        // Create a complete collection by using the ordered items of the mm-table by replacing the foreign uid with the
        // concrete object.
        $completeElements = [];

        while ($row = $statement->fetch()) {
            $completeElements[] = $objectStorageElementsByUid[$row['uid_foreign']];
        }

        return $completeElements;
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
            $getterMethodName = 'get' . ucfirst($property->getName());

            if (!$reflectionClass->hasMethod($getterMethodName)) {
                $getterMethodName = 'is' . ucfirst($property->getName());
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
