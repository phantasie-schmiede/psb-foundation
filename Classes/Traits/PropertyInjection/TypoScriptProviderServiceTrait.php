<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits\PropertyInjection;

use PSB\PsbFoundation\Service\TypoScriptProviderService;

/**
 * Trait TypoScriptProviderServiceTrait
 *
 * @package PSB\PsbFoundation\Traits\PropertyInjection
 */
trait TypoScriptProviderServiceTrait
{
    /**
     * @var TypoScriptProviderService
     */
    protected TypoScriptProviderService $typoScriptProviderService;

    /**
     * @param TypoScriptProviderService $typoScriptProviderService
     *
     * @return void
     */
    public function injectTypoScriptProviderService(TypoScriptProviderService $typoScriptProviderService): void
    {
        $this->typoScriptProviderService = $typoScriptProviderService;
    }
}
