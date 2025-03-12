<?php
declare(strict_types=1);

use PSB\PsbFoundation\EventListener\CacheConfigurationBuilder;
use PSB\PsbFoundation\Service\Configuration\PageTypeService;
use PSB\PsbFoundation\Service\Configuration\PluginService;
use PSB\PsbFoundation\Service\ExtensionInformationService;
use PSB\PsbFoundation\Service\GlobalVariableProviders\EarlyAccessConstantsProvider;
use PSB\PsbFoundation\Service\GlobalVariableProviders\RequestParameterProvider;
use PSB\PsbFoundation\Service\GlobalVariableProviders\SiteConfigurationProvider;
use PSB\PsbFoundation\Service\GlobalVariableService;
use PSB\PsbFoundation\Service\Typo3\LanguageServiceFactory;
use PSB\PsbFoundation\Utility\Configuration\FilePathUtility;
use PSB\PsbFoundation\Utility\FileUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory as Typo3LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') or die();

(static function() {
    // Overwrite LanguageServiceFactory to implement usage of plural forms in translations.
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][Typo3LanguageServiceFactory::class] = [
        'className' => LanguageServiceFactory::class,
    ];

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

        foreach ([
                     'user',
                     'User',
                 ] as $filename) {
            $userTsConfigFilename = FilePathUtility::EXTENSION_DIRECTORY_PREFIX . $extensionInformation->getExtensionKey(
                ) . '/Configuration/' . $filename . '.tsconfig';

            if (FileUtility::fileExists($userTsConfigFilename)) {
                ExtensionManagementUtility::addUserTSConfig('@import \'' . $userTsConfigFilename . '\'');
            }
        }
    }

    if (file_exists(Environment::getVarPath() . CacheConfigurationBuilder::FILE_PATHS['TSCONFIG'])) {
        $fileContents = file_get_contents(
            Environment::getVarPath() . CacheConfigurationBuilder::FILE_PATHS['TSCONFIG']
        );

        if (!empty(trim($fileContents))) {
            $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultPageTSconfig'] .= LF . file_get_contents(
                    Environment::getVarPath() . CacheConfigurationBuilder::FILE_PATHS['TSCONFIG']
                );
        }
    }

    if (file_exists(Environment::getVarPath() . CacheConfigurationBuilder::FILE_PATHS['TYPOSCRIPT'])) {
        $fileContents = file_get_contents(
            Environment::getVarPath() . CacheConfigurationBuilder::FILE_PATHS['TYPOSCRIPT']
        );

        if (!empty(trim($fileContents))) {
            ExtensionManagementUtility::addTypoScriptSetup(
                file_get_contents(
                    Environment::getVarPath() . CacheConfigurationBuilder::FILE_PATHS['TYPOSCRIPT']
                )
            );
        }
    }
})();
