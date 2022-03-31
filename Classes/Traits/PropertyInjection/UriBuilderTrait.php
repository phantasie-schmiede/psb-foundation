<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits\PropertyInjection;

use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

/**
 * Trait UriBuilderTrait
 *
 * @package PSB\PsbFoundation\Traits\PropertyInjection
 */
trait UriBuilderTrait
{
    /**
     * @var UriBuilder
     */
    protected UriBuilder $uriBuilder;

    /**
     * @param UriBuilder $uriBuilder
     */
    public function injectUriBuilder(UriBuilder $uriBuilder): void
    {
        $this->uriBuilder = $uriBuilder;
    }
}
