<?php
declare(strict_types=1);

use PSB\PsbFoundation\Service\Configuration\PluginService;
use PSB\PsbFoundation\Service\ExtensionInformationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') or die();

(static function () {
    // register all plugins of those extensions which provide an ExtensionInformation-class
    $extensionInformationService = GeneralUtility::makeInstance(ExtensionInformationService::class);
    $pluginService = GeneralUtility::makeInstance(PluginService::class);
    $allExtensionInformation = $extensionInformationService->getExtensionInformation();

    foreach ($allExtensionInformation as $extensionInformation) {
        $pluginService->registerPlugins($extensionInformation);
    }
})();
