<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\EventListener;

use Doctrine\DBAL\Exception as DbalException;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use PSB\PsbFoundation\Service\Configuration\PageCacheService;
use PSB\PsbFoundation\Utility\FileUtility;
use TYPO3\CMS\Core\Cache\Event\CacheFlushEvent;
use TYPO3\CMS\Core\Core\Environment;

/**
 * Class CacheConfigurationBuilder
 *
 * @package PSB\PsbFoundation\EventListener
 */
final readonly class CacheConfigurationBuilder
{
    public const array FILE_PATHS = [
        'TSCONFIG'   => '/cache/psb_foundation/TSconfig/cacheConfiguration.tsconfig',
        'TYPOSCRIPT' => '/cache/psb_foundation/TypoScript/cacheConfiguration.typoscript',
    ];

    public function __construct(
        protected PageCacheService $pageCacheService,
    ) {
    }

    /**
     * @throws DbalException
     * @throws Exception
     */
    #[NoReturn]
    public function __invoke(CacheFlushEvent $event): void
    {
        $basePath = Environment::getVarPath();
        FileUtility::write(
            $basePath . self::FILE_PATHS['TSCONFIG'],
            $this->pageCacheService->buildTSconfig(),
        );
        FileUtility::write(
            $basePath . self::FILE_PATHS['TYPOSCRIPT'],
            $this->pageCacheService->buildTypoScript(),
        );
    }
}
