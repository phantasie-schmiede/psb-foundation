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
use PSB\PsbFoundation\Utility\TypoScript\TypoScriptUtility;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class EarlyAccessConstantsProvider
 *
 * Extensions may provide a YAML-file with constants that can be used in ext_localconf.php-files (before TypoScript is
 * available). Those constants can be accessed via the GlobalVariableService and are registered as
 * TypoScript-constants, too.
 *
 * If your constants are neither needed that early during TYPO3's bootstrap process nor are they context-specific, you
 * may consider to place them in the config.yaml of your SiteConfiguration:
 * https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.4/Feature-91080-SiteSettingsAsTsConstantsAndInTsConfig.html
 *
 * To provide a simple configuration that is valid for all stages, just create the file
 * /Configuration/EarlyAccessConstants/constants.yaml inside your extension directory.
 * It is possible to provide context-specific files that enable you to manage the requirements of different stages. The
 * context is added to the directory structure whereas the last part serves as filename and is converted to lowercase.
 *
 * Examples:
 * /Configuration/EarlyAccessConstants/development.yaml
 * /Configuration/EarlyAccessConstants/production.yaml
 * /Configuration/EarlyAccessConstants/Production/staging.yaml
 *
 * @package PSB\PsbFoundation\Service\GlobalVariableProviders
 */
class EarlyAccessConstantsProvider implements GlobalVariableProviderInterface
{
    public const DIRECTORY = '/Configuration/EarlyAccessConstants/';

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

        // This builds the path for a context-specific file with a lowercase filename.
        $contextParts = explode('/', (string)Environment::getContext());
        $lastIndex = count($contextParts) - 1;
        $contextParts[$lastIndex] = lcfirst($contextParts[$lastIndex]);
        $filePath = self::DIRECTORY . implode('/', $contextParts) . '.yaml';

        foreach ($allExtensionInformation as $extensionInformation) {
            $yamlFile = GeneralUtility::getFileAbsFileName('EXT:' . $extensionInformation->getExtensionKey() . $filePath);

            if (!file_exists($yamlFile)) {
                $yamlFile = GeneralUtility::getFileAbsFileName('EXT:' . $extensionInformation->getExtensionKey() . self::DIRECTORY . 'constants.yaml');
            }

            if (file_exists($yamlFile)) {
                $constants = Yaml::parseFile($yamlFile);
                ExtensionManagementUtility::addTypoScriptConstants(TypoScriptUtility::convertArrayToTypoScript($constants));
                ArrayUtility::mergeRecursiveWithOverrule($mergedConstants, $constants);
            }
        }

        $this->setCacheable(true);

        return $mergedConstants;
    }

    /**
     * When returned data isn't supposed to change anymore, set function's return value to true.
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
