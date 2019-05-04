<?php
declare(strict_types=1);

namespace PS\PsFoundation\Services;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 Daniel Ablass <dn@phantasie-schmiede.de>, Phantasie-Schmiede
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Utility\ArrayUtility;
use function count;

/**
 * Class GlobalVariableService
 * @package PS\PsFoundation\Services
 */
class GlobalVariableService
{
    /**
     * @var array
     */
    protected static $globalVariableProviders = [];

    /**
     * @var array
     */
    protected static $globalVariables = [];

    /**
     * @param GlobalVariableProviderInterface $globalVariableProvider
     */
    public static function registerGlobalVariableProvider(GlobalVariableProviderInterface $globalVariableProvider): void
    {
        self::$globalVariableProviders[] = $globalVariableProvider;
    }

    /**
     * @return array
     */
    public static function getGlobalVariables(): array
    {
        if (0 === count(self::$globalVariables)) {
            /** @var GlobalVariableProviderInterface $globalVariableProvider */
            foreach (self::$globalVariableProviders as $globalVariableProvider) {
                ArrayUtility::mergeRecursiveWithOverrule(self::$globalVariables,
                    $globalVariableProvider->getGlobalVariables());
            }
        }

        return self::$globalVariables;
    }
}
