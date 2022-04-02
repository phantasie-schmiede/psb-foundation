<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits\PropertyInjection;

use TYPO3\CMS\Core\TypoScript\TypoScriptService;

/**
 * Trait TypoScriptServiceTrait
 *
 * @package PSB\PsbFoundation\Traits\PropertyInjection
 */
trait TypoScriptServiceTrait
{
    /**
     * @var TypoScriptService
     */
    protected TypoScriptService $typoScriptService;

    /**
     * @param TypoScriptService $typoScriptService
     *
     * @return void
     */
    public function injectTypoScriptService(TypoScriptService $typoScriptService): void
    {
        $this->typoScriptService = $typoScriptService;
    }
}
