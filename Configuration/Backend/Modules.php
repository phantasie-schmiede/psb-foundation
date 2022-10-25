<?php
declare(strict_types=1);

use PSB\PsbFoundation\Service\Configuration\RegistrationService;
use PSB\PsbFoundation\Service\ExtensionInformationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$extensionInformationService = GeneralUtility::makeInstance(ExtensionInformationService::class);
$allExtensionInformation = $extensionInformationService->getExtensionInformation();
$registrationService = GeneralUtility::makeInstance(RegistrationService::class);
$modules = [];

foreach ($allExtensionInformation as $extensionInformation) {
    $moduleConfigurations = $registrationService->buildModuleConfigurations($extensionInformation);

    foreach ($moduleConfigurations as $moduleConfiguration) {
        $modules[] = $moduleConfiguration;
    }
}

return $modules;
