<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits\PropertyInjection;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

/**
 * Trait ExtensionConfigurationTrait
 *
 * @package PSB\PsbFoundation\Traits\PropertyInjection
 */
trait ExtensionConfigurationTrait
{
    /**
     * @var ExtensionConfiguration
     */
    protected ExtensionConfiguration $extensionConfiguration;

    /**
     * @param ExtensionConfiguration $extensionConfiguration
     *
     * @return void
     */
    public function injectExtensionConfiguration(ExtensionConfiguration $extensionConfiguration): void
    {
        $this->extensionConfiguration = $extensionConfiguration;
    }
}
