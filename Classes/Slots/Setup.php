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

namespace PSB\PsbFoundation\Slots;

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
                'Data\ExtensionInformation',
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
