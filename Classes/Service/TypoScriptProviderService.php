<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Service;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019-2020 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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

use Exception;
use PSB\PsbFoundation\Traits\StaticInjectionTrait;
use PSB\PsbFoundation\Utility\StringUtility;
use RuntimeException;
use stdClass;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

/**
 * Class TypoScriptProviderService
 * @package PSB\PsbFoundation\Service
 */
class TypoScriptProviderService
{
    use StaticInjectionTrait;

    /**
     * @var bool
     */
    public static $fullTypoScriptAvailable = false;

    /**
     * @var array
     */
    private static $cachedTypoScript;

    /**
     * @var array
     */
    private static $preliminaryTypoScript;

    /**
     * @param string|null $path
     * @param string      $configurationType
     * @param string|null $extensionName
     * @param string|null $pluginName
     *
     * @return mixed
     * @throws InvalidConfigurationTypeException
     */
    public static function getTypoScriptConfiguration(
        string $path = null,
        string $configurationType = ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
        string $extensionName = null,
        string $pluginName = null
    ) {
        $typoScript = null;

        if (true === self::$fullTypoScriptAvailable && isset(self::$cachedTypoScript[$configurationType][$extensionName][$pluginName])) {
            return self::getDemandedTypoScript(self::$cachedTypoScript[$configurationType][$extensionName][$pluginName],
                $path);
        }

        // RootlineUtility:286 requires $GLOBALS['TCA'] to be set
        if (null !== $GLOBALS['TCA']) {
            $typoScript = self::get(ConfigurationManager::class)
                ->getConfiguration($configurationType, $extensionName, $pluginName);
            self::$fullTypoScriptAvailable = true;
        } elseif (isset(self::$preliminaryTypoScript[$configurationType][$extensionName][$pluginName])) {
            return self::getDemandedTypoScript(self::$preliminaryTypoScript[$configurationType][$extensionName][$pluginName],
                $path);
        }

        if (!is_array($typoScript)) {
            $typoScript = self::generateTypoScript($configurationType, $extensionName, $pluginName);
        }

        if (null === $typoScript) {
            return null;
        }

        $typoScript = self::get(TypoScriptService::class)->convertTypoScriptArrayToPlainArray($typoScript);

        array_walk_recursive($typoScript, static function (&$item) {
            // if constants are unset
            if (0 === mb_strpos($item, '{$')) {
                $item = null;
            } else {
                $item = StringUtility::convertString($item);
            }
        });

        if (true === self::$fullTypoScriptAvailable) {
            self::$cachedTypoScript[$configurationType][$extensionName][$pluginName] = $typoScript;
        } else {
            self::$preliminaryTypoScript[$configurationType][$extensionName][$pluginName] = $typoScript;
        }

        return self::getDemandedTypoScript($typoScript, $path);
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

        $templateService = self::get(TemplateService::class);
        $templateService->tt_track = false;
        $templateService->init();
        $templateService->runThroughTemplates([['uid' => 1]]);
        $templateService->generateConfig();

        $typoScript = $templateService->setup;

        if ($resetTsfe ?? false) {
            $GLOBALS['TSFE'] = null;
        }

        if (null !== $extensionName) {
            $key = 'tx_' . mb_strtolower($extensionName);

            if (null !== $pluginName) {
                $key .= '_' . mb_strtolower($pluginName);
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

    /**
     * @param array       $typoScript
     * @param string|null $path
     *
     * @return array|mixed
     */
    private static function getDemandedTypoScript(array $typoScript, string $path = null)
    {
        if (null !== $path) {
            try {
                return ArrayUtility::getValueByPath($typoScript, $path, '.');
            } catch (Exception $e) {
                throw new RuntimeException(__CLASS__ . ': Path "' . $path . '" does not exist in array', 1562225431);
            }
        }

        return $typoScript;
    }
}
