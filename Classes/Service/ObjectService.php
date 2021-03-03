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

namespace PSB\PsbFoundation\Service;

use JsonException;
use PSB\PsbFoundation\Service\DocComment\Annotations\TCA\Mm;
use PSB\PsbFoundation\Traits\PropertyInjection\ConnectionPoolTrait;
use PSB\PsbFoundation\Traits\PropertyInjection\DocCommentParserServiceTrait;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException;

/**
 * Class ObjectService
 *
 * @package PSB\PsbFoundation\Service
 */
class ObjectService
{
    use ConnectionPoolTrait, DocCommentParserServiceTrait;

    /**
     * @param AbstractDomainObject $object
     * @param string               $property
     *
     * @return array
     * @throws InvalidArgumentForHashGenerationException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function resolveMultipleMmRelation(AbstractDomainObject $object, string $property): array
    {
        $docComment = $this->docCommentParserService->parsePhpDocComment($object, $property);

        if (!isset($docComment[Mm::class])) {
            throw new RuntimeException(__CLASS__ . ': The property "' . $property . '" of object "' . get_class($object) . '" is not of TCA type mm!',
                1584867595);
        }

        // Store each ObjectStorage element by uid.
        $reflectionClass = GeneralUtility::makeInstance(ReflectionClass::class, $object);
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
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($mm->getMm());
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
}
