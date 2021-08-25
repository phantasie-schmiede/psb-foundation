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
use PSB\PsbFoundation\Data\ExtensionInformation;
use PSB\PsbFoundation\Exceptions\ImplementationException;
use PSB\PsbFoundation\Traits\PropertyInjection\ExtensionInformationServiceTrait;
use PSB\PsbFoundation\Traits\PropertyInjection\LocalizationServiceTrait;
use PSB\PsbFoundation\Utility\Configuration\FilePathUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Package\Event\AfterPackageActivationEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extensionmanager\Exception\ExtensionManagerException;
use TYPO3\CMS\Extensionmanager\Utility\InstallUtility;

/**
 * Class AfterPackageActivationEventListener
 *
 * @package PSB\PsbFoundation\EventListener
 */
class AfterPackageActivationEventListener
{
    use ExtensionInformationServiceTrait, LocalizationServiceTrait;

    /**
     * @var bool
     */
    protected static bool $psbFoundationHasJustBeenInstalled = false;

    /**
     * psb_foundation can't register itself in the same way as other extensions because neither the table exists nor the
     * event listeners have been registered yet. The registration is done by a static SQL statement in
     * ext_tables_static+adt.sql. For the same reasons, psb_foundation MUST NOT be "co-installed" as a dependency of
     * another extension!
     *
     * @param AfterPackageActivationEvent $event
     *
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws ExtensionManagerException
     * @throws ImplementationException
     */
    public function __invoke(AfterPackageActivationEvent $event): void
    {
        $extensionKey = $event->getPackageKey();

        if (true === $this::$psbFoundationHasJustBeenInstalled) {
            $installUtility = GeneralUtility::makeInstance(InstallUtility::class);
            $installUtility->uninstall($extensionKey);
            $languageFilePath = FilePathUtility::getLanguageFilePath();

            throw new ExtensionManagerException(
                $this->localizationService->translate($languageFilePath . 'error.noSingleInstallation', null,
                    ['extensionKey' => $extensionKey]),
                1624281342
            );
        }

        $extensionInformation = GeneralUtility::makeInstance(ExtensionInformation::class);

        if ($extensionInformation->getExtensionKey() === $extensionKey) {
            $this->extensionInformationService->register(ExtensionInformation::class, $extensionKey);
            $this::$psbFoundationHasJustBeenInstalled = true;
        }
    }
}
