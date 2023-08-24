<?php
declare(strict_types=1);

use PSB\PsbFoundation\Service\Configuration\PageTypeService;
use PSB\PsbFoundation\Service\Configuration\PluginService;
use PSB\PsbFoundation\Service\ExtensionInformationService;
use PSB\PsbFoundation\Service\GlobalVariableProviders\EarlyAccessConstantsProvider;
use PSB\PsbFoundation\Service\GlobalVariableProviders\RequestParameterProvider;
use PSB\PsbFoundation\Service\GlobalVariableProviders\SiteConfigurationProvider;
use PSB\PsbFoundation\Service\GlobalVariableService;
use PSB\PsbFoundation\Utility\Configuration\FilePathUtility;
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
    $pageTypeService = GeneralUtility::makeInstance(PageTypeService::class);
    $pluginService = GeneralUtility::makeInstance(PluginService::class);

    foreach ($extensionInformationService->getAllExtensionInformation() as $extensionInformation) {
        $pageTypeService->addToDragArea($extensionInformation);
        $pluginService->configurePlugins($extensionInformation);

        $userTSconfigFilename = FilePathUtility::EXTENSION_DIRECTORY_PREFIX . $extensionInformation->getExtensionKey() . '/Configuration/TSconfig/User/User.tsconfig';

        if (FileUtility::fileExists($userTSconfigFilename)) {
            ExtensionManagementUtility::addUserTSConfig('
             @import \'' . $userTSconfigFilename . '\'
        ');
        }
    }
})();
