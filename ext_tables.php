<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
defined('TYPO3_MODE') or die();

(static function () {
    // register all modules and domain model tables of those extensions which provide an ExtensionInformation-class
    $allExtensionInformation = \PSB\PsbFoundation\Utility\ExtensionInformationUtility::getExtensionInformation();

    foreach ($allExtensionInformation as $extensionInformation) {
        \PSB\PsbFoundation\Utility\Backend\RegistrationUtility::registerModules($extensionInformation);
        \PSB\PsbFoundation\Service\Configuration\TcaService::registerNewTablesInGlobalTca($extensionInformation);
    }
})();
