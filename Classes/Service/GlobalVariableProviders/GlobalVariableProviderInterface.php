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
     * You can use the ContextUtility to control if your provider should be called during this request and if it is
     * ready to be instantiated.
     *
     * Example:
     * This function has to return false during TYPO3's bootstrap process if the provider uses dependency
     * injection or TYPO3's CacheManager.
     * return ContextUtility::isBootProcessRunning ? null : true;
     *
     * @return bool|null Return false if your provider should not be loaded during this request.
     *                   Return true if your provider can be instantiated.
     *                   Return null if the instantiation of your provider shall be postponed.
     */
    public static function isAvailable(): ?bool;

    /**
     * @return array
     */
    public function getGlobalVariables(): array;

    /**
     * When returned data may change during the request, set function's return value to false. This function is
     * called after getGlobalVariables().
     *
     * @return bool
     */
    public function isCacheable(): bool;
}
