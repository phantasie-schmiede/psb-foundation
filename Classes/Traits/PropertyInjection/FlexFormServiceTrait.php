<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits\PropertyInjection;

use PSB\PsbFoundation\Service\Configuration\FlexFormService;

/**
 * Trait FlexFormServiceTrait
 *
 * @package PSB\PsbFoundation\Traits\PropertyInjection
 */
trait FlexFormServiceTrait
{
    /**
     * @var FlexFormService
     */
    protected FlexFormService $flexFormService;

    /**
     * @param FlexFormService $flexFormService
     *
     * @return void
     */
    public function injectFlexFormService(FlexFormService $flexFormService): void
    {
        $this->flexFormService = $flexFormService;
    }
}
