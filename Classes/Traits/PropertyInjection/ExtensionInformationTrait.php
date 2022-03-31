<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits\PropertyInjection;

use PSB\PsbFoundation\Data\ExtensionInformation;

/**
 * Trait ExtensionInformationTrait
 *
 * @package PSB\PsbFoundation\Traits\PropertyInjection
 */
trait ExtensionInformationTrait
{
    /**
     * @var ExtensionInformation
     */
    protected ExtensionInformation $extensionInformation;

    /**
     * @param ExtensionInformation $extensionInformation
     */
    public function injectExtensionInformation(ExtensionInformation $extensionInformation): void
    {
        $this->extensionInformation = $extensionInformation;
    }
}
