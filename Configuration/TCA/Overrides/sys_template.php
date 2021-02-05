<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types = 1);

defined('TYPO3_MODE') or die();

(static function () {
    // register TypoScript of those extensions which provide an ExtensionInformation-class
    $extensionInformationService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\PSB\PsbFoundation\Service\ExtensionInformationService::class);
    $allExtensionInformation = $extensionInformationService->getExtensionInformation();

    foreach ($allExtensionInformation as $extensionInformation) {
        \PSB\PsbFoundation\Utility\TypoScript\TypoScriptUtility::registerTypoScript($extensionInformation);
    }
})();
