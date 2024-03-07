<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Service\GlobalVariableProviders;

/**
 * Class AbstractProvider
 *
 * @package PSB\PsbFoundation\Service\GlobalVariableProviders
 */
abstract class AbstractProvider implements GlobalVariableProviderInterface
{
    protected bool $cacheable = true;

    /**
     * When returned data may change during a request, set function's return value to false.
     */
    public function isCacheable(): bool
    {
        return $this->cacheable;
    }

    public function setCacheable(bool $cacheable): void
    {
        $this->cacheable = $cacheable;
    }
}
