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

namespace PSB\PsbFoundation\Service;

use Exception;
use PSB\PsbFoundation\Utility\ObjectUtility;
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
    /**
     * @var bool
     */
    public static bool $fullTypoScriptAvailable = false;

    /**
     * @var array
     */
    private static array $cachedTypoScript;

    /**
     * @var array
     */
    private static array $preliminaryTypoScript;

    /**
     * @param string|null $path
     * @param string      $configurationType
     * @param string|null $extensionName
     * @param string|null $pluginName
     *
     * @return mixed
     * @throws InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Object\Exception
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
            $typoScript = ObjectUtility::get(ConfigurationManager::class)
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

        $typoScript = ObjectUtility::get(TypoScriptService::class)->convertTypoScriptArrayToPlainArray($typoScript);

        array_walk_recursive($typoScript, static function (&$item) {
            if (is_string($item)) {
                // if constants are not set
                if (0 === mb_strpos($item, '{$')) {
                    $item = null;
                } else {
                    $item = StringUtility::convertString($item);
                }
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
     * @throws \TYPO3\CMS\Extbase\Object\Exception
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

        $templateService = ObjectUtility::get(TemplateService::class);
        $templateService->tt_track = false;
        $templateService->init();
        $templateService->runThroughTemplates([['uid' => 1]]);
        $templateService->generateConfig();

        $typoScript = $templateService->setup;

        if (true === ($resetTsfe ?? false)) {
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
