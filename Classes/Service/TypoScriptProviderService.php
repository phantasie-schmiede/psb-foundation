<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Service;

use Exception;
use JsonException;
use PSB\PsbFoundation\Traits\PropertyInjection\ConfigurationManagerTrait;
use PSB\PsbFoundation\Traits\PropertyInjection\TypoScriptServiceTrait;
use PSB\PsbFoundation\Utility\StringUtility;
use PSB\PsbFoundation\Utility\ValidationUtility;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

/**
 * Class TypoScriptProviderService
 *
 * @package PSB\PsbFoundation\Service
 */
class TypoScriptProviderService
{
    use ConfigurationManagerTrait;
    use TypoScriptServiceTrait;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct()
    {
        ValidationUtility::requiresTypoScriptLoaded();
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
     * Wrapper function for the ConfigurationManager.
     *
     * This function returns the requested part of the TypoScript with converted value types. Unset constants are
     * replaced with NULL. By default, the whole TypoScript is taken into account. Example:
     * $this->typoScriptProviderService->get('config.headerComment');
     *
     * @param string|null $path concatenate path segments by '.'
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
                if (StringUtility::beginsWith($item, '{$')) {
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
