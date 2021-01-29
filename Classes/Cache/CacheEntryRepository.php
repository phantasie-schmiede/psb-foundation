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

namespace PSB\PsbFoundation\Cache;

use Doctrine\DBAL\FetchMode;
use PDO;
use PSB\PsbFoundation\Traits\InjectionTrait;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException;

/**
 * Class CacheEntryRepository
 *
 * This repository can be used before TYPO3's bootstrap process has finished.
 *
 * @package PSB\PsbFoundation\Cache
 */
class CacheEntryRepository
{
    use InjectionTrait;

    public const TABLE_NAME = 'cache_psbfoundation';

    /**
     * @param CacheEntry $cacheEntry
     *
     * @throws Exception
     * @throws InvalidArgumentForHashGenerationException
     */
    public function add(CacheEntry $cacheEntry): void
    {
        $cacheEntry->calculateCheckSum(true);
        $this->get(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE_NAME)->resetQueryParts()->insert(self::TABLE_NAME)->values([
                'checksum'   => $cacheEntry->getChecksum(),
                'content'    => $cacheEntry->getContent(),
                'identifier' => $cacheEntry->getIdentifier(),
            ])->execute();
    }

    /**
     * @param string $identifier
     *
     * @return CacheEntry|null
     * @throws Exception
     * @throws InvalidArgumentForHashGenerationException
     */
    public function findByIdentifier(string $identifier): ?CacheEntry
    {
        $queryBuilder = $this->get(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE_NAME);
        $cacheEntryRow = $queryBuilder->resetQueryParts()->select('*')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq(
                    'identifier',
                    $queryBuilder->createNamedParameter($identifier, PDO::PARAM_STR)
                )
            )->execute()
            ->fetch(FetchMode::ASSOCIATIVE);

        if (false === $cacheEntryRow) {
            return null;
        }

        $cacheEntry = $this->get(CacheEntry::class);
        $cacheEntry->setChecksum($cacheEntryRow['checksum']);
        $cacheEntry->setContent($cacheEntryRow['content']);
        $cacheEntry->setIdentifier($cacheEntryRow['identifier']);
        $cacheEntry->validateChecksum();

        return $cacheEntry;
    }
}
