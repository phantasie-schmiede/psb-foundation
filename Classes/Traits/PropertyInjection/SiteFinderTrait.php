<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits\PropertyInjection;

use TYPO3\CMS\Core\Site\SiteFinder;

/**
 * Trait SiteFinderTrait
 *
 * @package PSB\PsbFoundation\Traits\PropertyInjection
 */
trait SiteFinderTrait
{
    /**
     * @var SiteFinder
     */
    protected SiteFinder $siteFinder;

    /**
     * @param SiteFinder $siteFinder
     *
     * @return void
     */
    public function injectSiteFinder(SiteFinder $siteFinder): void
    {
        $this->siteFinder = $siteFinder;
    }
}
