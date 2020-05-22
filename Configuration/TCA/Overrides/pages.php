<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

defined('TYPO3_MODE') or die();

(static function () {
    $allExtensionInformation = \PSB\PsbFoundation\Utility\ExtensionInformationUtility::getExtensionInformation();

    foreach ($allExtensionInformation as $extensionInformation) {
        \PSB\PsbFoundation\Utility\Backend\RegistrationUtility::registerPageTypes($extensionInformation,
            \PSB\PsbFoundation\Utility\Backend\RegistrationUtility::PAGE_TYPE_REGISTRATION_MODES['TCA_OVERRIDE']);
    }
})();

