<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits\PropertyInjection;

use TYPO3\CMS\Core\Imaging\IconRegistry;

/**
 * Trait IconRegistryTrait
 *
 * @package PSB\PsbFoundation\Traits\PropertyInjection
 */
trait IconRegistryTrait
{
    /**
     * @var IconRegistry
     */
    protected IconRegistry $iconRegistry;

    /**
     * @param IconRegistry $iconRegistry
     */
    public function injectIconRegistry(IconRegistry $iconRegistry): void
    {
        $this->iconRegistry = $iconRegistry;
    }
}
