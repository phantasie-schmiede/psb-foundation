<?php

declare(strict_types=1);

use PSBits\Foundation\Service\RegisteredIconService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

return call_user_func(
    static function() {
        return GeneralUtility::makeInstance(RegisteredIconService::class)
            ->getIconRegistrationConfiguration();
    }
);
