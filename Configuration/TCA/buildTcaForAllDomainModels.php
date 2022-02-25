<?php
declare(strict_types = 1);

use PSB\PsbFoundation\Service\Configuration\TcaService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3_MODE') or die();

return call_user_func(
    static function () {
        $tcaService = GeneralUtility::makeInstance(TcaService::class);
        $tcaService->buildTca(false);
    }
);
