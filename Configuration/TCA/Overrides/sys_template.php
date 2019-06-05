<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

defined('TYPO3_MODE') or die();

(static function () {
    \PSB\PsbFoundation\Utilities\TypoScriptUtility::registerTypoScript(\PSB\PsbFoundation\Data\ExtensionInformation::class);
})();
