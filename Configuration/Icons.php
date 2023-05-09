<?php
declare(strict_types=1);

use PSB\PsbFoundation\Service\ExtensionInformationService;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

return call_user_func(
    static function () {
        $icons = [];
        $extensionInformationService = GeneralUtility::makeInstance(ExtensionInformationService::class);
        $allExtensionInformation = $extensionInformationService->getAllExtensionInformation();

        foreach ($allExtensionInformation as $extensionInformation) {
            $path = GeneralUtility::getFileAbsFileName('EXT:' . $extensionInformation->getExtensionKey() . '/Resources/Public/Icons');

            if (!is_dir($path)) {
                continue;
            }

            $finder = Finder::create()->files()->in($path)->name(['*.png', '*.svg']);

            /** @var SplFileInfo $fileInfo */
            foreach ($finder as $fileInfo) {
                $iconIdentifier = str_replace('_', '-',
                        $extensionInformation->getExtensionKey()) . '-' . str_replace('_', '-',
                        GeneralUtility::camelCaseToLowerCaseUnderscored($fileInfo->getFilenameWithoutExtension()));

                $icons[$iconIdentifier] = [
                    'provider' => ('svg' === strtolower($fileInfo->getExtension())) ? SvgIconProvider::class : BitmapIconProvider::class,
                    'source'   => $fileInfo->getRealPath(),
                ];
            }
        }

        return $icons;
    }
);
