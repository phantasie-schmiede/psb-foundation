<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace PSB\PsbFoundation\Service\GlobalVariableProviders;

/**
 * Interface GlobalVariableProviderInterface
 *
 * @package PSB\PsbFoundation\Service\GlobalVariableProviders
 */
interface GlobalVariableProviderInterface
{
    /**
     * This function has to return false if the provider uses e.g. dependency injection or TYPO3's CacheManager. Return
     * true if it can safely be instantiated and executed in ext_localconf.php files.
     *
     * @return bool
     */
    public static function isAvailableDuringBootProcess(): bool;

    /**
     * @return array
     */
    public function getGlobalVariables(): array;

    /**
     * This must return false on first call. Otherwise the function getGlobalVariables() will never be called. When
     * returned data isn't supposed to change anymore, set function's return value to true.
     *
     * @return bool
     */
    public function isCacheable(): bool;
}
