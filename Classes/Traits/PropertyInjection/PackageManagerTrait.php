<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits\PropertyInjection;

use TYPO3\CMS\Core\Package\PackageManager;

/**
 * Trait PackageManagerTrait
 *
 * @package PSB\PsbFoundation\Traits\PropertyInjection
 */
trait PackageManagerTrait
{
    /**
     * @var PackageManager
     */
    protected PackageManager $packageManager;

    /**
     * @param PackageManager $packageManager
     *
     * @return void
     */
    public function injectPackageManager(PackageManager $packageManager): void
    {
        $this->packageManager = $packageManager;
    }
}
