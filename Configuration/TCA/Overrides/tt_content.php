<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

defined('TYPO3_MODE') or die();

(static function () {
    // register all plugins of those extensions which provide an ExtensionInformation-class
    $extensionInformationClassNames = PSB\PsbFoundation\Utility\ExtensionInformationUtility::getRegisteredClassNames();

    foreach ($extensionInformationClassNames as $className) {
        /** @var PSB\PsbFoundation\Data\ExtensionInformationInterface $extensionInformation */
        $extensionInformation = \PSB\PsbFoundation\Utility\ObjectUtility::get($className);
        \PSB\PsbFoundation\Utility\Backend\RegistrationUtility::registerPlugins($extensionInformation);
    }
})();
