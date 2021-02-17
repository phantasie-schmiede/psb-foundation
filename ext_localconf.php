<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
defined('TYPO3_MODE') or die();

(static function () {
    \PSB\PsbFoundation\Service\GlobalVariableService::registerGlobalVariableProvider(\PSB\PsbFoundation\Service\GlobalVariableProviders\EarlyAccessConstantsProvider::class);
    \PSB\PsbFoundation\Service\GlobalVariableService::registerGlobalVariableProvider(\PSB\PsbFoundation\Service\GlobalVariableProviders\RequestParameterProvider::class);
    \PSB\PsbFoundation\Service\GlobalVariableService::registerGlobalVariableProvider(\PSB\PsbFoundation\Service\GlobalVariableProviders\SiteConfigurationProvider::class);

    // configure all plugins of those extensions which provide an ExtensionInformation-class and add TypoScript if missing
    $extensionInformationService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\PSB\PsbFoundation\Service\ExtensionInformationService::class);
    $iconService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\PSB\PsbFoundation\Service\Configuration\IconService::class);
    $registrationService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\PSB\PsbFoundation\Service\Configuration\RegistrationService::class);
    $allExtensionInformation = $extensionInformationService->getExtensionInformation();

    foreach ($allExtensionInformation as $extensionInformation) {
        $iconService->registerIconsFromExtensionDirectory($extensionInformation->getExtensionKey());
        $registrationService->configurePlugins($extensionInformation);
        \PSB\PsbFoundation\Utility\TypoScript\TypoScriptUtility::addDefaultTypoScriptForPluginsAndModules($extensionInformation);

        $userTsConfigFilename = 'EXT:' . $extensionInformation->getExtensionKey() . '/Configuration/TSConfig/UserTS.tsconfig';

        if (\PSB\PsbFoundation\Utility\FileUtility::fileExists($userTsConfigFilename)) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('
             <INCLUDE_TYPOSCRIPT: source="FILE:' . $userTsConfigFilename . '">
        ');
        }
    }

    $typoScriptParser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\PSB\PsbFoundation\Service\Configuration\ValueParsers\TypoScriptParser::class);
    \PSB\PsbFoundation\Service\Configuration\FlexFormService::addValueParser($typoScriptParser);

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Fluid\View\TemplateView::class]['className'] = \PSB\PsbFoundation\Views\TemplateView::class;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Fluid\ViewHelpers\RenderViewHelper::class]['className'] = \PSB\PsbFoundation\ViewHelpers\RenderViewHelper::class;

    // DocCommentParser-cache
    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['psbfoundation'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['psbfoundation'] = [];
    }
})();
