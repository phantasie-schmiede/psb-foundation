<?php
declare(strict_types=1);

namespace PSB\PsbFoundation\Utilities\Backend;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Extensionmanager\Utility\InstallUtility;

/**
 * Class SetupUtility
 * @package PSB\PsbFoundation\Utilities\Backend
 */
class SetupUtility
{
    /**
     * @param string $className
     * @param string $onInstallMethod
     * @param string $onUninstallMethod
     */
    public static function registerSetupSlots(
        string $className,
        string $onInstallMethod = 'onInstall',
        string $onUninstallMethod = 'onUninstall'
    ): void {
        // context conditions should not be placed inside ext_localconf.php (https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ExtensionArchitecture/ConfigurationFiles/Index.html#best-practices-for-ext-tables-php-and-ext-localconf-php)
        if ('BE' === TYPO3_MODE) {
            $dispatcher = GeneralUtility::makeInstance(ObjectManager::class)->get(Dispatcher::class);

            $dispatcher->connect(
                InstallUtility::class,
                'afterExtensionInstall',
                $className,
                $onInstallMethod
            );

            $dispatcher->connect(
                InstallUtility::class,
                'afterExtensionUninstall',
                $className,
                $onUninstallMethod
            );
        }
    }
}
