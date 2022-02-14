<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

use PSB\PsbFoundation\Service\LocalizationService;
use Symfony\Component\Finder\Finder;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3_MODE') or die();

(static function () {
    // register TypoScript of those extensions which provide an ExtensionInformation-class
    $extensionInformationService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\PSB\PsbFoundation\Service\ExtensionInformationService::class);
    $allExtensionInformation = $extensionInformationService->getExtensionInformation();
    $localizationService = GeneralUtility::makeInstance(LocalizationService::class);

    foreach ($allExtensionInformation as $extensionInformation) {
        $path = ExtensionManagementUtility::extPath($extensionInformation->getExtensionKey()) . 'Configuration/TypoScript';

        if (!is_dir($path)) {
            continue;
        }

        $finder = Finder::create()->files()->in($path)->name('*.typoscript');

        if (true === $finder->hasResults()) {
            $title = 'LLL:EXT:' . $extensionInformation->getExtensionKey() . '/Resources/Private/Language/Backend/Configuration/TCA/Overrides/sys_template.xlf:template.title';

            if (false === $localizationService->translationExists($title)) {
                $title = 'Main configuration';
            }

            \PSB\PsbFoundation\Utility\TypoScript\TypoScriptUtility::registerTypoScript($extensionInformation, $path,
                $title);
        }
    }
})();
