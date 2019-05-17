<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
defined('TYPO3_MODE') or die();

call_user_func(
    static function ($extensionKey) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
             <INCLUDE_TYPOSCRIPT: source="FILE:EXT:'.$extensionKey.'/Configuration/TSConfig/PageTS.typoscript">
        ');

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('
             <INCLUDE_TYPOSCRIPT: source="FILE:EXT:'.$extensionKey.'/Configuration/TSConfig/UserTS.typoscript">
        ');

        $docCommentParser = \PSB\PsbFoundation\Utilities\ObjectUtility::get(\PSB\PsbFoundation\Services\DocComment\DocCommentParserService::class);
        $tcaFieldConfigParser = \PSB\PsbFoundation\Utilities\ObjectUtility::get(\PSB\PsbFoundation\Services\DocComment\ValueParsers\TcaFieldConfigParser::class);
        $docCommentParser->addValueParser($tcaFieldConfigParser,
            \PSB\PsbFoundation\Services\DocComment\DocCommentParserService::VALUE_TYPES['MERGE']);
        $tcaConfigParser = \PSB\PsbFoundation\Utilities\ObjectUtility::get(\PSB\PsbFoundation\Services\DocComment\ValueParsers\TcaConfigParser::class);
        $docCommentParser->addValueParser($tcaConfigParser,
            \PSB\PsbFoundation\Services\DocComment\DocCommentParserService::VALUE_TYPES['MERGE']);
        $tcaMappingParser = \PSB\PsbFoundation\Utilities\ObjectUtility::get(\PSB\PsbFoundation\Services\DocComment\ValueParsers\TcaMappingParser::class);
        $docCommentParser->addValueParser($tcaMappingParser,
            \PSB\PsbFoundation\Services\DocComment\DocCommentParserService::VALUE_TYPES['MERGE']);

        $typoScriptParser = \PSB\PsbFoundation\Utilities\ObjectUtility::get(\PSB\PsbFoundation\Services\Configuration\ValueParsers\TypoScriptParser::class);
        \PSB\PsbFoundation\Services\Configuration\FlexFormService::addValueParser($typoScriptParser);

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
    },
    'psb_foundation'
);
