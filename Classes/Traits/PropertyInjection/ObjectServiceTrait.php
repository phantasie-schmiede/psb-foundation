<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits\PropertyInjection;

use PSB\PsbFoundation\Service\ObjectService;

/**
 * Trait ObjectServiceTrait
 *
 * @package PSB\PsbFoundation\Traits\PropertyInjection
 */
trait ObjectServiceTrait
{
    /**
     * @var ObjectService
     */
    protected ObjectService $objectService;

    /**
     * @param ObjectService $objectService
     *
     * @return void
     */
    public function injectObjectService(ObjectService $objectService): void
    {
        $this->objectService = $objectService;
    }
}
