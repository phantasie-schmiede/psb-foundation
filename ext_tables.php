<?php
declare(strict_types=1);

use PSB\PsbFoundation\Service\Configuration\RegistrationService;
use PSB\PsbFoundation\Service\ExtensionInformationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3_MODE') or die();

(static function () {
    $extensionInformationService = GeneralUtility::makeInstance(ExtensionInformationService::class);
    $registrationService = GeneralUtility::makeInstance(RegistrationService::class);
    $allExtensionInformation = $extensionInformationService->getExtensionInformation();

    foreach ($allExtensionInformation as $extensionInformation) {
        $registrationService->registerPageTypes($extensionInformation,
            RegistrationService::PAGE_TYPE_REGISTRATION_MODES['EXT_TABLES']);
    }
})();
