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

use PSB\PsbFoundation\Exceptions\ImplementationException;
use PSB\PsbFoundation\Traits\Properties\ExtensionInformationServiceTrait;
use TYPO3\CMS\Core\Package\Event\AfterPackageActivationEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AfterPackageActivationEventListener
 *
 * @package PSB\PsbFoundation\EventListener
 */
class AfterPackageActivationEventListener
{
    use ExtensionInformationServiceTrait;

    /**
     * @param AfterPackageActivationEvent $event
     *
     * @throws ImplementationException
     */
    public function __invoke(AfterPackageActivationEvent $event): void
    {
        $fileName = GeneralUtility::getFileAbsFileName('EXT:' . $event->getPackageKey() . '/Classes/Data/ExtensionInformation.php');
        $vendorName = $this->extensionInformationService->extractVendorNameFromFile($fileName);

        if (null !== $vendorName) {
            $extensionInformationClassName = implode('\\', [
                $vendorName,
                GeneralUtility::underscoredToUpperCamelCase($event->getPackageKey()),
                'Data\ExtensionInformation',
            ]);
            $this->extensionInformationService->register($extensionInformationClassName, $event->getPackageKey());
        }
    }
}
