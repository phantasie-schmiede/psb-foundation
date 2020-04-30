<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
defined('TYPO3_MODE') or die();

(static function () {
    /** @var \PSB\PsbFoundation\Service\GlobalVariableProviders\GlobalVariableProviderInterface $siteConfigurationProvider */
    $siteConfigurationProvider = \PSB\PsbFoundation\Utility\ObjectUtility::get(\PSB\PsbFoundation\Service\GlobalVariableProviders\SiteConfigurationProvider::class);
    \PSB\PsbFoundation\Service\GlobalVariableService::registerGlobalVariableProvider($siteConfigurationProvider);

    // register all modules and domain model tables of those extensions which provide an ExtensionInformation-class
    $allExtensionInformation = \PSB\PsbFoundation\Utility\ExtensionInformationUtility::getExtensionInformation();

    foreach ($allExtensionInformation as $extensionInformation) {
        \PSB\PsbFoundation\Utility\Backend\IconUtility::registerIconsFromExtensionDirectory($extensionInformation->getExtensionKey());
        \PSB\PsbFoundation\Utility\Backend\RegistrationUtility::registerModules($extensionInformation);
        \PSB\PsbFoundation\Utility\Backend\RegistrationUtility::registerPageTypes($extensionInformation,
            \PSB\PsbFoundation\Utility\Backend\RegistrationUtility::PAGE_TYPE_REGISTRATION_MODES['EXT_TABLES']);
        \PSB\PsbFoundation\Service\Configuration\TcaService::registerNewTablesInGlobalTca($extensionInformation);
    }
})();
