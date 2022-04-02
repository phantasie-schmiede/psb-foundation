<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits\PropertyInjection;

use PSB\PsbFoundation\Service\ExtensionInformationService;

/**
 * Trait ExtensionInformationServiceTrait
 *
 * @package PSB\PsbFoundation\Traits\PropertyInjection
 */
trait ExtensionInformationServiceTrait
{
    /**
     * @var ExtensionInformationService
     */
    protected ExtensionInformationService $extensionInformationService;

    /**
     * @param ExtensionInformationService $extensionInformationService
     *
     * @return void
     */
    public function injectExtensionInformationService(ExtensionInformationService $extensionInformationService): void
    {
        $this->extensionInformationService = $extensionInformationService;
    }
}
