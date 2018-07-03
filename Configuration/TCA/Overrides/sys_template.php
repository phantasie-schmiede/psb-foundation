<?php
defined('TYPO3_MODE') or die();

call_user_func(
    function ($extKey) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extKey, 'Configuration/TypoScript',
            'LLL:EXT:'.$extKey.'/Resources/Private/Language/locallang_be.xlf:typoscript.label');
    },
    'ps_foundation'
);
