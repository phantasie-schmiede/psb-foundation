<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
defined('TYPO3_MODE') or die();

(static function () {
    // configure all plugins of those extensions which provide an ExtensionInformation-class and add TypoScript if missing
    $allExtensionInformation = \PSB\PsbFoundation\Utility\ExtensionInformationUtility::getExtensionInformation();

    foreach ($allExtensionInformation as $extensionInformation) {
        \PSB\PsbFoundation\Utility\Backend\RegistrationUtility::configurePlugins($extensionInformation);
        \PSB\PsbFoundation\Utility\TypoScript\TypoScriptUtility::addDefaultTypoScriptForPluginsAndModules($extensionInformation);
    }

    $extensionInformation = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\PSB\PsbFoundation\Data\ExtensionInformation::class);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
             <INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $extensionInformation->getExtensionKey() . '/Configuration/TSConfig/PageTS.tsconfig">
        ');

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('
             <INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $extensionInformation->getExtensionKey() . '/Configuration/TSConfig/UserTS.tsconfig">
        ');

    \PSB\PsbFoundation\Utility\Backend\SetupUtility::registerSetupSlots(\PSB\PsbFoundation\Slots\Setup::class);

    $typoScriptParser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\PSB\PsbFoundation\Service\Configuration\ValueParsers\TypoScriptParser::class);
    \PSB\PsbFoundation\Service\Configuration\FlexFormService::addValueParser($typoScriptParser);

    $docCommentCacheIdentifier = \PSB\PsbFoundation\Service\DocComment\DocCommentParserService::getCacheIdentifier();

    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$docCommentCacheIdentifier])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$docCommentCacheIdentifier] = [];
    }

    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$docCommentCacheIdentifier]['backend'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$docCommentCacheIdentifier]['backend'] = \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class;
    }

    // customize BE login style
    // $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['backend'] = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['backend'], ['allowed_classes' => false]);
    //
    // $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['backend']['loginLogo'] = 'EXT:startgreen/Resources/Public/Images/Backend/typo3-gruene_loginLogo.jpg';
    // $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['backend']['loginBackgroundImage'] = 'EXT:startgreen/Resources/Public/Images/Backend/typo3-gruene_loginBackgroundImage3.jpg';
    // $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['backend']['backendLogo'] = 'EXT:startgreen/Resources/Public/Images/Backend/typo3-gruene_backendLogo@2x.png';
    // $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['backend']['backendFavicon'] = 'EXT:startgreen/Resources/Public/Css/buendnis-90-die-gruenen.ico';
    // $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['backend']['loginHighlightColor'] = '#e6007e';
    //
    // $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['backend'] = serialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['backend']);
})();
