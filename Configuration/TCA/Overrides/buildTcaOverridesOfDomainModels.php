<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types = 1);

defined('TYPO3_MODE') or die();

return call_user_func(
    static function () {
        $tcaService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\PSB\PsbFoundation\Service\Configuration\TcaService::class);
        $tcaService->buildTca(true);
    }
);
