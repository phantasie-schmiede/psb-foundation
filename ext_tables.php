<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
defined('TYPO3_MODE') or die();

(static function () {
    // register all modules and domain model tables of those extensions which provide an ExtensionInformation-class
    $extensionInformationService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\PSB\PsbFoundation\Service\ExtensionInformationService::class);
    $registrationService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\PSB\PsbFoundation\Service\Configuration\RegistrationService::class);
    $allExtensionInformation = $extensionInformationService->getExtensionInformation();

    foreach ($allExtensionInformation as $extensionInformation) {
        $registrationService->registerModules($extensionInformation);
        $registrationService->registerPageTypes($extensionInformation,
            $registrationService::PAGE_TYPE_REGISTRATION_MODES['EXT_TABLES']);
        \PSB\PsbFoundation\Utility\Configuration\TcaUtility::registerNewTablesInGlobalTca($extensionInformation);
    }
})();
