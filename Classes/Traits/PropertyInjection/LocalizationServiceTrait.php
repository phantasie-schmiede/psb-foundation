<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits\PropertyInjection;

use PSB\PsbFoundation\Service\LocalizationService;

/**
 * Trait LocalizationServiceTrait
 *
 * @package PSB\PsbFoundation\Traits\PropertyInjection
 */
trait LocalizationServiceTrait
{
    /**
     * @var LocalizationService
     */
    protected LocalizationService $localizationService;

    /**
     * @param LocalizationService $localizationService
     */
    public function injectLocalizationService(LocalizationService $localizationService): void
    {
        $this->localizationService = $localizationService;
    }
}
