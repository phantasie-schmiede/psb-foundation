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

namespace PSB\PsbFoundation\EventListener;

use Doctrine\DBAL\Driver\Exception;
use PSB\PsbFoundation\Exceptions\ImplementationException;
use PSB\PsbFoundation\Traits\PropertyInjection\ExtensionInformationServiceTrait;
use PSB\PsbFoundation\Traits\PropertyInjection\LocalizationServiceTrait;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\Event\BeforePackageActivationEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extensionmanager\Domain\Model\Extension;

/**
 * Class BeforePackageActivationEventListener
 *
 * @package PSB\PsbFoundation\EventListener
 */
class BeforePackageActivationEventListener
{
    use ExtensionInformationServiceTrait, LocalizationServiceTrait;

    /**
     * @param BeforePackageActivationEvent $event
     *
     * @throws Exception
     * @throws ImplementationException
     */
    public function __invoke(BeforePackageActivationEvent $event): void
    {
        $extensionKeys = [];

        /** @var Extension $packageInformation */
        foreach ($event->getPackageKeys() as $packageInformation) {
            $extensionKeys[] = $packageInformation->getExtensionKey();
        }

        /** @var Extension $packageInformation */
        foreach ($extensionKeys as $extensionKey) {
            $fileName = Environment::getExtensionsPath() . '/' . $extensionKey . '/Classes/Data/ExtensionInformation.php';
            $vendorName = $this->extensionInformationService->extractVendorNameFromFile($fileName);

            if (null !== $vendorName) {
                $extensionInformationClassName = implode('\\', [
                    $vendorName,
                    GeneralUtility::underscoredToUpperCamelCase($extensionKey),
                    'Data\ExtensionInformation',
                ]);
                $this->extensionInformationService->register($extensionInformationClassName, $extensionKey);
            }
        }
    }
}
