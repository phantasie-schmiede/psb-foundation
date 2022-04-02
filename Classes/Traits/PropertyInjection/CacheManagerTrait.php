<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits\PropertyInjection;

use TYPO3\CMS\Core\Cache\CacheManager;

/**
 * Trait CacheManagerTrait
 *
 * @package PSB\PsbFoundation\Traits\PropertyInjection
 */
trait CacheManagerTrait
{
    /**
     * @var CacheManager
     */
    protected CacheManager $cacheManager;

    /**
     * @param CacheManager $cacheManager
     *
     * @return void
     */
    public function injectCacheManager(CacheManager $cacheManager): void
    {
        $this->cacheManager = $cacheManager;
    }
}
