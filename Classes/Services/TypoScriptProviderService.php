<?php
declare(strict_types=1);

namespace PS\PsFoundation\Services;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 Daniel Ablass <dn@phantasie-schmiede.de>, Phantasie-Schmiede
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use PS\PsFoundation\Traits\Injections\ObjectManagerStaticTrait;
use PS\PsFoundation\Utilities\VariableUtility;
use stdClass;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

/**
 * Class TypoScriptProviderService
 * @package PS\PsFoundation\Services
 */
class TypoScriptProviderService
{
    use ObjectManagerStaticTrait;

    /**
     * @var bool
     */
    public static $fullTypoScriptAvailable = false;

    /**
     * @param string      $configurationType
     * @param string|null $extensionName
     * @param string|null $pluginName
     *
     * @return array|null
     * @throws InvalidConfigurationTypeException
     */
    public static function getTypoScriptConfiguration(
        string $configurationType = ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
        string $extensionName = null,
        string $pluginName = null
    ): ?array {
        $configurationManager = self::getObjectManager()->get(ConfigurationManager::class);
        $typoScript = null;

        // RootlineUtility:286 requires $GLOBALS['TCA'] to be set
        if (null !== $GLOBALS['TCA']) {
            $typoScript = $configurationManager->getConfiguration($configurationType, $extensionName, $pluginName);
            self::$fullTypoScriptAvailable = true;
        }

        if (!is_array($typoScript)) {
            $typoScript = self::generateTypoScript($configurationType, $extensionName, $pluginName);
        }

        if (null === $typoScript) {
            return null;
        }

        $typoScriptService = self::getObjectManager()->get(TypoScriptService::class);
        $convertedTypoScript = $typoScriptService->convertTypoScriptArrayToPlainArray($typoScript);

        array_walk_recursive($convertedTypoScript, static function (&$item) {
            // if constants are unset
            if (0 === strpos($item, '{$')) {
                $item = null;
            } else {
                $item = VariableUtility::convertString($item);
            }
        });

        return $convertedTypoScript;
    }

    /**
     * This function is used, when TS is needed, but not yet provided by the general TYPO3 bootstrap process, e.g. in
     * ext_localconf.php. Results may differ from those returned by the ConfigurationManager later on, so check the
     * outcome carefully!
     *
     * @param string      $configurationType
     * @param string|null $extensionName
     * @param string|null $pluginName
     *
     * @return array
     */
    private static function generateTypoScript(
        string $configurationType,
        string $extensionName = null,
        string $pluginName = null
    ): array {
        if (null === $GLOBALS['TSFE']) {
            // fill $GLOBALS['TSFE'] with an empty object because in TemplateService:558 an object is assumed
            $GLOBALS['TSFE'] = new stdClass();
            $resetTsfe = true;
        }

        $templateService = self::getObjectManager()->get(TemplateService::class);
        $templateService->tt_track = false;
        $templateService->init();
        $templateService->runThroughTemplates([['uid' => 1]]);
        $templateService->generateConfig();

        $typoScript = $templateService->setup;

        if ($resetTsfe ?? false) {
            $GLOBALS['TSFE'] = null;
        }

        if (null !== $extensionName) {
            $key = 'tx_'.strtolower($extensionName);

            if (null !== $pluginName) {
                $key .= '_'.strtolower($pluginName);
            }

            $key .= '.';
        }

        switch ($configurationType) {
            case ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK:
                return $typoScript['plugin.'][$key];
                break;
            case ConfigurationManager::CONFIGURATION_TYPE_SETTINGS:
                return $typoScript['plugin.'][$key]['settings.'];
                break;
            default:
                return $typoScript;
                break;
        }
    }
}
