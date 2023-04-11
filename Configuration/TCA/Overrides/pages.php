<?php
declare(strict_types=1);

use PSB\PsbFoundation\Service\Configuration\PageTypeService;
use PSB\PsbFoundation\Service\ExtensionInformationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') or die();

(static function () {
    $extensionInformationService = GeneralUtility::makeInstance(ExtensionInformationService::class);
    $pageTypeService = GeneralUtility::makeInstance(PageTypeService::class);

    foreach ($extensionInformationService->getExtensionInformation() as $extensionInformation) {
        $pageTypeService->addToSelectBox($extensionInformation);
    }
})();

