<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019-2020 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class ObjectUtility
 * @package PSB\PsbFoundation\Utility
 */
class ObjectUtility
{
    public const NAMESPACE_FALLBACK_KEY = '__fallback';

    /**
     * @param string $className
     * @param array  $arguments
     *
     * @return object
     * @throws Exception
     */
    public static function get(string $className, ...$arguments)
    {
        if (GeneralUtility::getContainer()->get('boot.state')->done) {
            return GeneralUtility::makeInstance(ObjectManager::class)->get($className, ...$arguments);
        }

        return GeneralUtility::makeInstance($className, ...$arguments);
    }

    /**
     * @param string $className
     * @param array  $namespaces
     *
     * @return bool|string
     */
    public static function getFullQualifiedClassName(string $className, array $namespaces)
    {
        if (class_exists($className)) {
            return $className;
        }

        if (isset($namespaces[$className])) {
            return $namespaces[$className];
        }

        if (isset($namespaces[self::NAMESPACE_FALLBACK_KEY])) {
            $fallbackClassName = $namespaces[self::NAMESPACE_FALLBACK_KEY] . '\\' . $className;

            if (class_exists($fallbackClassName)) {
                return $fallbackClassName;
            }
        }

        return false;
    }
}
