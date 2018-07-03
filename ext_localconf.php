<?php
defined('TYPO3_MODE') or die();

call_user_func(
    function ($extKey) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
             <INCLUDE_TYPOSCRIPT: source="FILE:EXT:'.$extKey.'/Configuration/TSConfig/PageTS.typoscript">
        ');

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
    'ps_foundation'
);
