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

use PSB\PsbFoundation\Exceptions\ImplementationException;
use PSB\PsbFoundation\Traits\Properties\ExtensionInformationServiceTrait;
use PSB\PsbFoundation\Traits\Properties\ExtensionInformationTrait;
use PSB\PsbFoundation\Traits\Properties\ObjectServiceTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Setup
 *
 * @package PSB\PsbFoundation\Slots
 */
class Setup
{
    use ExtensionInformationTrait, ExtensionInformationServiceTrait, ObjectServiceTrait;

    /**
     * @param string $extensionKey
     *
     * @throws ImplementationException
     */
    public function onInstall(string $extensionKey): void
    {
        $fileName = GeneralUtility::getFileAbsFileName('EXT:' . $extensionKey . '/Classes/Data/ExtensionInformation.php');
        $vendorName = $this->extensionInformationService->extractVendorNameFromFile($fileName);

        if (null !== $vendorName) {
            $extensionInformationClassName = implode('\\', [
                $vendorName,
                GeneralUtility::underscoredToUpperCamelCase($extensionKey),
                'Data\ExtensionInformation',
            ]);
            $this->extensionInformationService->register($extensionInformationClassName, $extensionKey);
        }

        if ($this->extensionInformation->getExtensionKey() !== $extensionKey) {
            return;
        }
    }

    /**
     * @param string $extensionKey
     */
    public function onUninstall(string $extensionKey): void
    {
        $this->extensionInformationService->deregister($extensionKey);

        if ($this->extensionInformation->getExtensionKey() !== $extensionKey) {
            return;
        }
    }
}
