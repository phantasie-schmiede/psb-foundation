<?php
declare(strict_types=1);

namespace PSB\PsbFoundation\Data;

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

use PSB\PsbFoundation\Exceptions\ImplementationException;
use PSB\PsbFoundation\Utilities\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractExtensionInformation
 * @package PSB\PsbFoundation\Data
 */
abstract class AbstractExtensionInformation implements ExtensionInformationInterface
{
    public const EXTENSION_INFORMATION = [
        //        'EXTENSION_KEY' => '',
        //        'MODULES'       => [
        //            'submoduleKey' => [\Your\Module\Controller::class, \Your\Module\AnotherController::class]
        //        ],
        //        'PLUGINS'       => [
        //            'pluginName' => [\Your\Plugin\Controller::class, \Your\Plugin\AnotherController::class]
        //        ],
        //        'VENDOR_NAME'   => '',
    ];

    /**
     * @return string
     */
    public static function getExtensionKey(): string
    {
        if (isset(static::EXTENSION_INFORMATION['EXTENSION_KEY'])) {
            return static::EXTENSION_INFORMATION['EXTENSION_KEY'];
        }

        // @TODO: find out, why third parameter has to be passed although it is optional
        throw ObjectUtility::get(ImplementationException::class,
            static::getExceptionMessage('EXTENSION_KEY'), 1559635462, null);
    }

    /**
     * @return string
     */
    public static function getExtensionName(): string
    {
        if (isset(static::EXTENSION_INFORMATION['EXTENSION_KEY'])) {
            return GeneralUtility::underscoredToUpperCamelCase(static::EXTENSION_INFORMATION['EXTENSION_KEY']);
        }

        throw ObjectUtility::get(ImplementationException::class,
            static::getExceptionMessage('EXTENSION_KEY'), null);
    }

    /**
     * @return array|null
     */
    public static function getModules(): ?array
    {
        return static::EXTENSION_INFORMATION['MODULES'] ?? null;
    }

    /**
     * @return array|null
     */
    public static function getPlugins(): ?array
    {
        return static::EXTENSION_INFORMATION['PLUGINS'] ?? null;
    }

    /**
     * @return string
     */
    public static function getVendorName(): string
    {
        if (isset(static::EXTENSION_INFORMATION['VENDOR_NAME'])) {
            return static::EXTENSION_INFORMATION['VENDOR_NAME'];
        }

        throw ObjectUtility::get(ImplementationException::class,
            static::getExceptionMessage('VENDOR_NAME'), null);
    }

    /**
     * @param string $arrayKey
     *
     * @return string
     */
    private static function getExceptionMessage(string $arrayKey): string
    {
        return static::class.' has to define the constant EXTENSION_INFORMATION[\''.$arrayKey.'\']!';
    }
}
