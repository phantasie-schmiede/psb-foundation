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

namespace PSB\PsbFoundation\Utility\Backend;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Extensionmanager\Utility\InstallUtility;

/**
 * Class SetupUtility
 * @package PSB\PsbFoundation\Utility\Backend
 */
class SetupUtility
{
    /**
     * @param string $className
     * @param string $onInstallMethod
     * @param string $onUninstallMethod
     *
     * @throws Exception
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
