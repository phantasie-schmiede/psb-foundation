<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
defined('TYPO3_MODE') or die();

(static function () {
    $siteConfigurationProvider = \PSB\PsbFoundation\Utility\ObjectUtility::get(\PSB\PsbFoundation\Service\GlobalVariableProviders\SiteConfigurationProvider::class);
    \PSB\PsbFoundation\Service\GlobalVariableService::registerGlobalVariableProvider($siteConfigurationProvider);
    $requestParameterProvider = \PSB\PsbFoundation\Utility\ObjectUtility::get(\PSB\PsbFoundation\Service\GlobalVariableProviders\RequestParameterProvider::class);
    \PSB\PsbFoundation\Service\GlobalVariableService::registerGlobalVariableProvider($requestParameterProvider);

    // configure all plugins of those extensions which provide an ExtensionInformation-class and add TypoScript if missing
    $allExtensionInformation = \PSB\PsbFoundation\Utility\ExtensionInformationUtility::getExtensionInformation();

    foreach ($allExtensionInformation as $extensionInformation) {
        \PSB\PsbFoundation\Utility\Backend\IconUtility::registerIconsFromExtensionDirectory($extensionInformation->getExtensionKey());
        \PSB\PsbFoundation\Utility\Backend\RegistrationUtility::configurePlugins($extensionInformation);
        \PSB\PsbFoundation\Utility\TypoScript\TypoScriptUtility::addDefaultTypoScriptForPluginsAndModules($extensionInformation);

        $userTsConfigFilename = 'EXT:' . $extensionInformation->getExtensionKey() . '/Configuration/TSConfig/UserTS.tsconfig';

        if (\PSB\PsbFoundation\Utility\FileUtility::fileExists($userTsConfigFilename)) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('
             <INCLUDE_TYPOSCRIPT: source="FILE:' . $userTsConfigFilename . '">
        ');
        }
    }

    \PSB\PsbFoundation\Utility\Backend\SetupUtility::registerSetupSlots(\PSB\PsbFoundation\Slots\Setup::class);

    $typoScriptParser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\PSB\PsbFoundation\Service\Configuration\ValueParsers\TypoScriptParser::class);
    \PSB\PsbFoundation\Service\Configuration\FlexFormService::addValueParser($typoScriptParser);

    // @TODO: may be removed because only static functions are overridden?
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Extbase\Utility\LocalizationUtility::class] = [
        'className' => \PSB\PsbFoundation\Utility\LocalizationUtility::class,
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Fluid\View\TemplateView::class]['className'] = \PSB\PsbFoundation\Views\TemplateView::class;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Fluid\ViewHelpers\RenderViewHelper::class]['className'] = \PSB\PsbFoundation\ViewHelpers\RenderViewHelper::class;

    // DocCommentParser-cache
    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['psbfoundation'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['psbfoundation'] = [];
    }
})();
