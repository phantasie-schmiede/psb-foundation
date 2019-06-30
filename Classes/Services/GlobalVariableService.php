<?php
declare(strict_types=1);

namespace PSB\PsbFoundation\Services;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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

use Exception;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class GlobalVariableService
 * @package PSB\PsbFoundation\Services
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
     * @param string $path
     *
     * @return array
     */
    public static function getExplodedCsv(string $path): array
    {
        $csv = ArrayUtility::getValueByPath(self::$globalVariables, $path, '.');

        return GeneralUtility::trimExplode(',', $csv, true);
    }

    /**
     * @param string|null $path
     *
     * @return array
     */
    public static function getGlobalVariables(string $path = null): array
    {
        /** @var GlobalVariableProviderInterface $globalVariableProvider */
        foreach (self::$globalVariableProviders as $globalVariableProvider) {
            if (false === $globalVariableProvider->isCacheable()) {
                ArrayUtility::mergeRecursiveWithOverrule(self::$globalVariables,
                    $globalVariableProvider->getGlobalVariables());
            }
        }

        if (null !== $path) {
            return ArrayUtility::getValueByPath(self::$globalVariables, $path, '.');
        }

        return self::$globalVariables;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public static function has(string $path): bool
    {
        try {
            ArrayUtility::getValueByPath(self::getGlobalVariables(), $path, '.');

            return true;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param GlobalVariableProviderInterface $globalVariableProvider
     */
    public static function registerGlobalVariableProvider(GlobalVariableProviderInterface $globalVariableProvider): void
    {
        self::$globalVariableProviders[] = $globalVariableProvider;
    }
}
