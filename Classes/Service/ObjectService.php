<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Service;

use Exception;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Mm;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Select;
use PSB\PsbFoundation\Utility\ReflectionUtility;
use ReflectionClass;
use RuntimeException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use function get_class;

/**
 * Class ObjectService
 *
 * @package PSB\PsbFoundation\Service
 */
class ObjectService
{
    /**
     * @param ConnectionPool $connectionPool
     */
    public function __construct(
        protected readonly ConnectionPool $connectionPool,
    ) {
    }

    /**
     * If you have a select field in TCA with 'multiple' set to true, Extbase still returns each selected record only
     * once. This method returns the whole selected set sorted as in backend.
     *
     * @param AbstractDomainObject $object
     * @param string               $property
     *
     * @return array
     * @throws Exception
     */
    public function resolveMultipleMmRelation(AbstractDomainObject $object, string $property): array
    {
        // Store each ObjectStorage element by uid.
        $reflectionClass = new ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($property);

        $selectConfiguration = ReflectionUtility::getAttributeInstance(Select::class, $reflectionProperty);

        if (!$selectConfiguration instanceof Select) {
            throw new RuntimeException(__CLASS__ . ': The property "' . $property . '" of object "' . get_class($object) . '" is not of TCA type select!',
                1584867595);
        }

        if (empty($selectConfiguration->getMm())) {
            throw new RuntimeException(__CLASS__ . ': The select attribute of the property "' . $property . '" of object "' . get_class($object) . '" does not define a mm-table!',
                                       1687382027);
        }

        $objectStorageElements = $reflectionProperty->getValue($object);
        $objectStorageElementsByUid = [];

        /** @var AbstractDomainObject $element */
        foreach ($objectStorageElements as $element) {
            $objectStorageElementsByUid[$element->getUid()] = $element;
        }

        // Get all mm-relation entries.
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($selectConfiguration->getMm());
        $statement = $queryBuilder->select('uid_foreign')
            ->from($selectConfiguration->getMm())
            ->where($queryBuilder->expr()
                ->eq('uid_local', $queryBuilder->createNamedParameter($object->getUid())))
            ->orderBy('sorting')
            ->executeQuery();

        // Create a complete collection by using the ordered items of the mm-table by replacing the foreign uid with the
        // concrete object.
        $completeElements = [];

        while ($row = $statement->fetchAssociative()) {
            $completeElements[] = $objectStorageElementsByUid[$row['uid_foreign']];
        }

        return $completeElements;
    }
}
