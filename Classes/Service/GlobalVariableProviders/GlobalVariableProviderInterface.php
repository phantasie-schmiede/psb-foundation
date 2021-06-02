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
     * Each provider has to define a unique key which serves as entry point for the available data. Example:
     * 'myExt-myKey'
     * Your key must not contain a dot!
     *
     * @return string
     */
    public static function getKey(): string;

    /**
     * @return mixed
     */
    public function getGlobalVariables();

    /**
     * When returned data may change during the request, set function's return value to false. This function is
     * called after getGlobalVariables().
     *
     * @return bool
     */
    public function isCacheable(): bool;
}
