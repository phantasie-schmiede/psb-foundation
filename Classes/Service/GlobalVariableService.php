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

namespace PSB\PsbFoundation\Service;

use Exception;
use PSB\PsbFoundation\Service\GlobalVariableProviders\GlobalVariableProviderInterface;
use PSB\PsbFoundation\Utility\VariableUtility;
use RuntimeException;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class GlobalVariableService
 *
 * @package PSB\PsbFoundation\Service
 */
class GlobalVariableService
{
    /**
     * @var array
     */
    protected static array $globalVariableProviders = [];

    /**
     * @var array
     */
    protected static array $globalVariables = [];

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
     * @return mixed
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     */
    public static function getGlobalVariables(string $path = null)
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        foreach (self::$globalVariableProviders as $globalVariableProvider) {
            $globalVariableProvider = $objectManager->get($globalVariableProvider);

            if (!$globalVariableProvider instanceof GlobalVariableProviderInterface) {
                throw new RuntimeException(__CLASS__ . ': Class does not implement the required GlobalVariableProviderInterface!',
                    1612426722);
            }

            if (false === $globalVariableProvider->isCacheable()) {
                ArrayUtility::mergeRecursiveWithOverrule(self::$globalVariables,
                    $globalVariableProvider->getGlobalVariables());
            }
        }

        if (null !== $path) {
            try {
                return VariableUtility::getValueByPath(self::$globalVariables, $path);
            } catch (Exception $e) {
                throw new RuntimeException(__CLASS__ . ': Path "' . $path . '" does not exist in array', 1562136068);
            }
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
     * For use in ext_localconf.php
     *
     * @param string $className
     */
    public static function registerGlobalVariableProvider(string $className): void
    {
        self::$globalVariableProviders[] = $className;
    }
}
