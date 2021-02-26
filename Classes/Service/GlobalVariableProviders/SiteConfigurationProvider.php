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

namespace PSB\PsbFoundation\Service\GlobalVariableProviders;

use PSB\PsbFoundation\Traits\Properties\SiteFinderTrait;
use PSB\PsbFoundation\Utility\ContextUtility;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;

/**
 * Class SiteConfigurationProvider
 *
 * @package PSB\PsbFoundation\Service\GlobalVariableProviders
 */
class SiteConfigurationProvider implements GlobalVariableProviderInterface
{
    use SiteFinderTrait;

    public const KEY = 'siteConfiguration';

    /**
     * @var bool
     */
    protected bool $cacheable = false;

    /**
     * @return bool
     */
    public static function isAvailableDuringBootProcess(): bool
    {
        return false;
    }

    /**
     * @return array
     * @throws SiteNotFoundException
     */
    public function getGlobalVariables(): array
    {
        // not available in backend
        if (ContextUtility::isBackend()) {
            $this->setCacheable(true);

            return [];
        }

        $site = $this->siteFinder->getSiteByPageId((int)$GLOBALS['TSFE']->id);
        $this->setCacheable(true);

        return [self::KEY => $site];
    }

    /**
     * When returned data isn't supposed to change anymore, set function's return value to true.
     *
     * @return bool
     */
    public function isCacheable(): bool
    {
        return $this->cacheable;
    }

    /**
     * @param bool $cacheable
     */
    public function setCacheable(bool $cacheable): void
    {
        $this->cacheable = $cacheable;
    }
}
