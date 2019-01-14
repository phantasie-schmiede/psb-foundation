<?php

namespace PS\PsFoundation\Utilities;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018 PSG Web Team <webdev@plan.de>, PSG Plan Service Gesellschaft mbH
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
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Cache\Backend\FileBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

/**
 * Class Cache
 * @package PS\PsFoundation\Utilities
 */
class Cache implements SingletonInterface
{
    /**
     * @var array
     */
    private static $cacheTables = [];

    /**
     * @param string $cacheTable
     */
    public static function addCacheTable(string $cacheTable): void
    {
        if (\in_array($cacheTable, self::$cacheTables, true)) {
            return;
        }

        self::$cacheTables[] = $cacheTable;
    }

    /**
     * @param array  $records
     * @param string $className
     *
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     */
    public static function addCacheTagsByRecords(array $records, string $className): void
    {
        $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
        $tagPrefix = $dataMapper->getDataMap($className)->getTableName();

        $cacheTags = [];
        foreach ($records as $record) {
            $cacheTags[] = $tagPrefix.'_uid_'.$record->getUid();
        }

        if (\count($cacheTags) > 0) {
            $GLOBALS['TSFE']->addCacheTags($cacheTags);
        }
    }

    /**
     * @param array $params
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheGroupException
     */
    public function clearCachePostProc(array $params): void
    {
        if (!isset($params['table']) || !\in_array($params['table'], self::$cacheTables, true)) {
            return;
        }

        if (isset($params['uid'])) {
            /** @var CacheManager $cacheManager */
            $cacheManager = GeneralUtility::makeInstance(CacheManager::class);

            $cacheManager->flushCachesInGroupByTag('pages', $params['table'].'_uid_'.$params['uid']);
        }
    }

    /**
     * @param string $tag
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheGroupException
     */
    public static function removeCacheEntryByCacheTag(string $tag): void
    {
        /** @var ObjectManagerInterface $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var CacheManager $cacheManager */
        $cacheManager = $objectManager->get(CacheManager::class);

        $cacheManager->flushCachesInGroupByTag('pages', $tag);
    }

    /**
     * @param string $identifier
     *
     * @return FrontendInterface
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    public static function getCache(string $identifier): FrontendInterface
    {
        /** @var ObjectManagerInterface $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var CacheManager $cacheManager */
        $cacheManager = $objectManager->get(CacheManager::class);

        // caching implementations for plugin(s)
        if (false === $cacheManager->hasCache($identifier) && empty($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$identifier])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$identifier] = ['backend' => FileBackend::class];
            $cacheManager->setCacheConfigurations($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']);
        }

        return $cacheManager->getCache($identifier);
    }
}
