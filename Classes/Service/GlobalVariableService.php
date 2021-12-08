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
    protected static array $cachedVariables = [];

    /**
     * @var array
     */
    protected static array $globalVariableProviders = [];

    /**
     * @param string $path
     *
     * @return mixed
     */
    public static function get(string $path)
    {
        $globalVariables = self::$cachedVariables;

        try {
            return VariableUtility::getValueByPath($globalVariables, $path);
        } catch (Exception $e) {
            // Do nothing.
        }

        $pathElements = explode('.', $path);
        $key = array_shift($pathElements);

        if (!isset(self::$globalVariableProviders[$key])) {
            throw new RuntimeException(__CLASS__ . ': Key "' . $key . '" is not registered! Available keys are: ' . implode(', ',
                    array_keys(self::$globalVariableProviders)) . '.',
                1622575130);
        }

        if (!self::$globalVariableProviders[$key] instanceof GlobalVariableProviderInterface) {
            self::$globalVariableProviders[$key] = GeneralUtility::makeInstance($key);
        }

        $variables = [$key => self::$globalVariableProviders[$key]->getGlobalVariables()];
        ArrayUtility::mergeRecursiveWithOverrule($globalVariables, $variables);

        if (true === self::$globalVariableProviders[$key]->isCacheable()) {
            self::$cachedVariables = $globalVariables;
        }

        return VariableUtility::getValueByPath($globalVariables, $path);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public static function has(string $path): bool
    {
        try {
            self::get($path);

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
        if (!in_array(GlobalVariableProviderInterface::class, class_implements($className), true)) {
            throw new RuntimeException(__CLASS__ . ': Class does not implement the required GlobalVariableProviderInterface!',
                1612426722);
        }

        // The value is just a placeholder for the instance which might be instantiated.
        self::$globalVariableProviders[$className] = '';
    }
}
