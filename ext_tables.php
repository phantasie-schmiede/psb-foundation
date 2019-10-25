<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
defined('TYPO3_MODE') or die();

(static function () {
    // register all modules and domain model tables of those extensions which provide an ExtensionInformation-class
    $extensionInformationClassNames = PSB\PsbFoundation\Utility\ExtensionInformationUtility::getRegisteredClassNames();

    foreach ($extensionInformationClassNames as $className) {
        /** @var PSB\PsbFoundation\Data\ExtensionInformationInterface $extensionInformation */
        $extensionInformation = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($className);
        \PSB\PsbFoundation\Utility\Backend\RegistrationUtility::registerModules($extensionInformation);
        \PSB\PsbFoundation\Service\Configuration\TcaService::registerNewTablesInGlobalTca($extensionInformation);
    }
})();
