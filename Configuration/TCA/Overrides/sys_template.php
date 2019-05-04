<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

defined('TYPO3_MODE') or die();

call_user_func(
    static function ($extKey) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extKey, 'Configuration/TypoScript',
            'LLL:EXT:'.$extKey.'/Resources/Private/Language/locallang_be.xlf:typoscript.label');
    },
    'ps_foundation'
);
