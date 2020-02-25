<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */

defined('TYPO3_MODE') || die('Access denied.');

/**
 * @see \PSB\PsbFoundation\Utility\Backend\TcaUtility::buildTca
 */
return call_user_func(
    static function () {
        \PSB\PsbFoundation\Utility\Backend\TcaUtility::buildTca(true);
    }
);
