<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits;

use TYPO3\CMS\Core\Cache\CacheDataCollector;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\CacheTag;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheGroupException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Trait ControllerCacheTrait
 *
 * Define this constant in your controller:
 * public const CACHE_TAG = 'controller_cache_trait';
 *
 * @package PSB\PsbFoundation\Traits
 */
trait ControllerCacheTrait
{
    /**
     * @throws NoSuchCacheGroupException
     */
    private function deletePagesCache(): void
    {
        /** @var CacheManager $cacheManager */
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cacheManager->flushCachesInGroupByTag('pages', self::CACHE_TAG);
    }

    private function setCacheTag(): void
    {
        /** @var CacheDataCollector $cacheDataCollector */
        $cacheDataCollector = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.cache.collector');
        $cacheDataCollector->addCacheTags(new CacheTag(self::CACHE_TAG));
    }
}
