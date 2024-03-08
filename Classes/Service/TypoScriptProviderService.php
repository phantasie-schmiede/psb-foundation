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
use PSB\PsbFoundation\Utility\StringUtility;
use PSB\PsbFoundation\Utility\ValidationUtility;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use function is_string;

/**
 * Class TypoScriptProviderService
 *
 * @package PSB\PsbFoundation\Service
 */
class TypoScriptProviderService
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        protected readonly ConfigurationManagerInterface $configurationManager,
        protected readonly TypoScriptService             $typoScriptService,
    ) {
        ValidationUtility::requiresTypoScriptLoaded();
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
     * @throws ContainerExceptionInterface
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function get(
        string $path = null,
        string $configurationType = ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
        string $extensionName = null,
        string $pluginName = null,
    ): mixed {
        $typoScript = $this->configurationManager->getConfiguration($configurationType, $extensionName, $pluginName);
        $typoScript = $this->typoScriptService->convertTypoScriptArrayToPlainArray($typoScript);

        array_walk_recursive($typoScript, static function(&$item) {
            if (is_string($item)) {
                // if constants are not set
                if (str_starts_with($item, '{$')) {
                    $item = null;
                } else {
                    $item = StringUtility::convertString($item);
                }
            }
        });

        return $this->getDemandedTypoScript($typoScript, $path);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function has(
        string $path,
        string $configurationType = ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
        string $extensionName = null,
        string $pluginName = null,
    ): bool {
        try {
            $this->get($path, $configurationType, $extensionName, $pluginName);

            return true;
        } catch (Exception) {
            return false;
        }
    }

    private function getDemandedTypoScript(array $typoScript, string $path = null): mixed
    {
        if (null !== $path) {
            try {
                return ArrayUtility::getValueByPath($typoScript, $path, '.');
            } catch (Exception) {
                throw new RuntimeException(__CLASS__ . ': Path "' . $path . '" does not exist in array', 1562225431);
            }
        }

        return $typoScript;
    }
}
