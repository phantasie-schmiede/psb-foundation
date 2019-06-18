<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

defined('TYPO3_MODE') or die();

(static function () {
    $extensionInformation = \PSB\PsbFoundation\Utilities\ObjectUtility::get(\PSB\PsbFoundation\Data\ExtensionInformation::class);
    \PSB\PsbFoundation\Utilities\TypoScriptUtility::registerTypoScript($extensionInformation);
})();
