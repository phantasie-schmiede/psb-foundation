<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace PSB\PsbFoundation\Service\GlobalVariableProviders;

use PSB\PsbFoundation\Service\ExtensionInformationService;
use PSB\PsbFoundation\Utility\FileUtility;
use PSB\PsbFoundation\Utility\TypoScript\TypoScriptUtility;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class EarlyAccessConstantsProvider
 *
 * @package PSB\PsbFoundation\Service\GlobalVariableProviders
 */
class EarlyAccessConstantsProvider implements GlobalVariableProviderInterface
{
    /**
     * @var bool
     */
    protected bool $cacheable = false;

    /**
     * @return bool
     */
    public static function isAvailableDuringBootProcess(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function getGlobalVariables(): array
    {
        $mergedConstants = [];
        $extensionInformationService = GeneralUtility::makeInstance(ExtensionInformationService::class);
        $allExtensionInformation = $extensionInformationService->getExtensionInformation();

        foreach ($allExtensionInformation as $extensionInformation) {
            $yamlFile = GeneralUtility::getFileAbsFileName('EXT:' . $extensionInformation->getExtensionKey() . '/Configuration/TypoScript/EarlyAccessConstants.yaml');

            if (FileUtility::fileExists($yamlFile)) {
                $constants = Yaml::parse(file_get_contents($yamlFile));
                ExtensionManagementUtility::addTypoScriptConstants(TypoScriptUtility::convertArrayToTypoScript($constants));
                ArrayUtility::mergeRecursiveWithOverrule($mergedConstants, $constants);
            }
        }

        $this->setCacheable(true);

        return $mergedConstants;
    }

    /**
     * This must return false on first call. Otherwise the function getGlobalVariables() will never be called. When
     * returned data isn't supposed to change anymore, set function's return value to true.
     *
     * @return bool
     */
    public function isCacheable(): bool
    {
        return $this->cacheable;
    }

    /**
     * @param bool $cacheable
     */
    public function setCacheable(bool $cacheable): void
    {
        $this->cacheable = $cacheable;
    }
}
