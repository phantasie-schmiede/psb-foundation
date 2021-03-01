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
use JsonException;
use PSB\PsbFoundation\Utility\StringUtility;
use PSB\PsbFoundation\Utility\ValidationUtility;
use RuntimeException;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

/**
 * Class TypoScriptProviderService
 *
 * @package PSB\PsbFoundation\Service
 */
class TypoScriptProviderService
{
    /**
     * @var ConfigurationManager
     */
    protected ConfigurationManager $configurationManager;

    /**
     * @var TypoScriptService
     */
    protected TypoScriptService $typoScriptService;

    /**
     * TypoScriptProviderService constructor.
     *
     * @param ConfigurationManager $configurationManager
     * @param TypoScriptService    $typoScriptService
     */
    public function __construct(ConfigurationManager $configurationManager, TypoScriptService $typoScriptService)
    {
        ValidationUtility::requiresBootstrappingDone();
        $this->configurationManager = $configurationManager;
        $this->typoScriptService = $typoScriptService;
    }

    /**
     * @param array       $typoScript
     * @param string|null $path
     *
     * @return mixed
     */
    private function getDemandedTypoScript(array $typoScript, string $path = null)
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

    /**
     * @param string|null $path
     * @param string      $configurationType
     * @param string|null $extensionName
     * @param string|null $pluginName
     *
     * @return mixed
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     */
    public function get(
        string $path = null,
        string $configurationType = ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
        string $extensionName = null,
        string $pluginName = null
    ) {
        $typoScript = $this->configurationManager->getConfiguration($configurationType, $extensionName, $pluginName);
        $typoScript = $this->typoScriptService->convertTypoScriptArrayToPlainArray($typoScript);

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

        return $this->getDemandedTypoScript($typoScript, $path);
    }

    /**
     * @param string      $path
     * @param string      $configurationType
     * @param string|null $extensionName
     * @param string|null $pluginName
     *
     * @return bool
     */
    public function has(
        string $path,
        string $configurationType = ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
        string $extensionName = null,
        string $pluginName = null
    ): bool {
        try {
            $this->get($path, $configurationType, $extensionName, $pluginName);

            return true;
        } catch (Exception $exception) {
            return false;
        }
    }
}
