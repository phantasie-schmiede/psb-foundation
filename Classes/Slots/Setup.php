<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Slots;

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

use PSB\PsbFoundation\Data\ExtensionInformation;
use PSB\PsbFoundation\Exceptions\ImplementationException;
use PSB\PsbFoundation\Utility\ExtensionInformationUtility;
use PSB\PsbFoundation\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class Setup
 * @package PSB\PsbFoundation\Slots
 */
class Setup
{
    /**
     * @param string $extensionKey
     *
     * @throws ImplementationException
     * @throws Exception
     */
    public function onInstall(string $extensionKey): void
    {
        $fileName = GeneralUtility::getFileAbsFileName('EXT:' . $extensionKey . '/Classes/Data/ExtensionInformation.php');
        $vendorName = ExtensionInformationUtility::extractVendorNameFromFile($fileName);

        if (null !== $vendorName) {
            $extensionInformationClassName = implode('\\', [
                $vendorName,
                GeneralUtility::underscoredToUpperCamelCase($extensionKey),
                'Data',
                'ExtensionInformation',
            ]);
            ExtensionInformationUtility::register($extensionInformationClassName, $extensionKey);
        }

        $extensionInformation = ObjectUtility::get(ExtensionInformation::class);

        if ($extensionInformation->getExtensionKey() !== $extensionKey) {
            return;
        }
    }

    /**
     * @param string $extensionKey
     *
     * @throws Exception
     */
    public function onUninstall(string $extensionKey): void
    {
        ExtensionInformationUtility::deregister($extensionKey);
        $extensionInformation = ObjectUtility::get(ExtensionInformation::class);

        if ($extensionInformation->getExtensionKey() !== $extensionKey) {
            return;
        }
    }
}
