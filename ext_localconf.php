<?php
declare(strict_types=1);

use PSB\PsbFoundation\Service\Configuration\RegistrationService;
use PSB\PsbFoundation\Service\ExtensionInformationService;
use PSB\PsbFoundation\Service\GlobalVariableProviders\EarlyAccessConstantsProvider;
use PSB\PsbFoundation\Service\GlobalVariableProviders\RequestParameterProvider;
use PSB\PsbFoundation\Service\GlobalVariableProviders\SiteConfigurationProvider;
use PSB\PsbFoundation\Service\GlobalVariableService;
use PSB\PsbFoundation\Utility\FileUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') or die();

(static function () {
    GlobalVariableService::registerGlobalVariableProvider(EarlyAccessConstantsProvider::class);
    GlobalVariableService::registerGlobalVariableProvider(RequestParameterProvider::class);
    GlobalVariableService::registerGlobalVariableProvider(SiteConfigurationProvider::class);

    // configure all plugins of those extensions which provide an ExtensionInformation-class and add TypoScript if missing
    $extensionInformationService = GeneralUtility::makeInstance(ExtensionInformationService::class);
    $registrationService = GeneralUtility::makeInstance(RegistrationService::class);
    $allExtensionInformation = $extensionInformationService->getExtensionInformation();

    foreach ($allExtensionInformation as $extensionInformation) {
        $registrationService->configurePlugins($extensionInformation);
        $pageTsConfigFilename = 'EXT:' . $extensionInformation->getExtensionKey() . '/Configuration/TsConfig/Page/Page.tsconfig';

        if (FileUtility::fileExists($pageTsConfigFilename)) {
            ExtensionManagementUtility::addPageTSConfig('
             @import \'' . $pageTsConfigFilename . '\'
        ');
        }

        $userTsConfigFilename = 'EXT:' . $extensionInformation->getExtensionKey() . '/Configuration/TsConfig/User/User.tsconfig';

        if (FileUtility::fileExists($userTsConfigFilename)) {
            ExtensionManagementUtility::addUserTSConfig('
             @import \'' . $userTsConfigFilename . '\'
        ');
        }
    }
})();
