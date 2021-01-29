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

use PSB\PsbFoundation\Traits\InjectionTrait;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class SiteConfigurationProvider
 *
 * @package PSB\PsbFoundation\Service\GlobalVariableProviders
 */
class SiteConfigurationProvider implements GlobalVariableProviderInterface
{
    use InjectionTrait;

    /**
     * @var bool
     */
    protected bool $cacheable = false;

    /**
     * @return array
     * @throws Exception
     * @throws SiteNotFoundException
     */
    public function getGlobalVariables(): array
    {
        // not available in backend
        if ('BE' === TYPO3_MODE) {
            $this->setCacheable(true);

            return [];
        }

        $site = $this->get(SiteFinder::class)->getSiteByPageId((int)$GLOBALS['TSFE']->id);
        $this->setCacheable(true);

        return ['siteConfiguration' => $site];
    }

    /**
     * This must return false on first call. Otherwise the function getGlobalVariables() will never be called. When
     * returned data isn't supposed to change anymore, set function's return value to true.
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
