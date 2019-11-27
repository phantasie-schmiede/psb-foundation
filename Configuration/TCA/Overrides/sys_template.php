<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

defined('TYPO3_MODE') or die();

(static function () {
    // register TypoScript of those extensions which provide an ExtensionInformation-class
    $extensionInformationClassNames = PSB\PsbFoundation\Utility\ExtensionInformationUtility::getRegisteredClassNames();

    foreach ($extensionInformationClassNames as $className) {
        /** @var PSB\PsbFoundation\Data\ExtensionInformationInterface $extensionInformation */
        $extensionInformation = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance($className);
        \PSB\PsbFoundation\Utility\TypoScript\TypoScriptUtility::registerTypoScript($extensionInformation);
    }
})();
