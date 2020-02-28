<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Cache;

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
