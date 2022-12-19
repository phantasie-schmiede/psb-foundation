<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\ViewHelpers;

use Closure;
use InvalidArgumentException;
use PSB\PsbFoundation\Service\LocalizationService;
use PSB\PsbFoundation\Utility\ContextUtility;
use PSB\PsbFoundation\Utility\StringUtility;
use PSB\PsbFoundation\ViewHelpers\Translation\RegisterLanguageFileViewHelper;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use ValueError;
use function count;

/**
 * Class TranslateViewHelper
 *
 * Overwrites the core ViewHelper in order to use \PSB\PsbFoundation\Service\LocalizationService which is able to log
 * missing language labels.
 *
 * @package PSB\PsbFoundation\ViewHelpers
 */
class TranslateViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\TranslateViewHelper
{
    /**
     * Return array element by key.
     *
     * @param array                     $arguments
     * @param Closure                   $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return null|string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function renderStatic(
        array $arguments,
        Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext,
    ): ?string {
        [
            'arguments'     => $translateArguments,
            'default'       => $default,
            'extensionName' => $extensionName,
            'id'            => $id,
            'key'           => $key,
        ] = $arguments;

        // Use key if id is empty.
        if (null === $id) {
            $id = $key;
        }

        if ('' === (string)$id) {
            throw new Exception('An argument "key" or "id" has to be provided', 1351584844);
        }

        $request = $renderingContext->getRequest();

        if (null === $extensionName && !StringUtility::beginsWith($id, 'LLL:')) {
            $extensionName = $request->getControllerExtensionName();
            $id = self::buildId($id, $renderingContext, $request);
        }

        try {
            $value = static::translate($id, $extensionName, $translateArguments, $arguments['languageKey'],
                $arguments['alternativeLanguageKeys']);
        } catch (InvalidArgumentException) {
            $value = null;
        }

        if (null === $value) {
            $value = $default ?? $renderChildrenClosure();
        }

        if (null !== $value && !empty($translateArguments)) {
            self::formatVolatileString($value, $translateArguments);
        }

        return $value;
    }

    /**
     * Wrapper call to static LocalizationService
     *
     * @param string   $id                      Translation Key
     * @param string   $extensionName           UpperCamelCased extension key (for example BlogExample)
     * @param array    $arguments               Arguments to be replaced in the resulting string
     * @param string   $languageKey             Language key to use for this translation
     * @param string[] $alternativeLanguageKeys Alternative language keys if no translation does exist
     *
     * @return string|null
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected static function translate(
        $id,
        $extensionName,
        $arguments,
        $languageKey,
        $alternativeLanguageKeys,
    ): ?string {
        return GeneralUtility::makeInstance(LocalizationService::class)
            ->translate($id, $extensionName, $arguments, $languageKey, $alternativeLanguageKeys);
    }

    /**
     * @param string                    $id
     * @param RenderingContextInterface $renderingContext
     * @param Request                   $request
     *
     * @return string
     */
    private static function buildId(string $id, RenderingContextInterface $renderingContext, Request $request): string
    {
        if (0 < mb_strpos($id, ':')) {
            [$alias, $id] = GeneralUtility::trimExplode(':', $id);
            $templateVariableContainer = $renderingContext->getVariableProvider();

            if ($templateVariableContainer->exists(RegisterLanguageFileViewHelper::VARIABLE_NAME)) {
                $registry = $templateVariableContainer->get(RegisterLanguageFileViewHelper::VARIABLE_NAME);

                if (isset($registry[RegisterLanguageFileViewHelper::REGISTRY_KEY][$alias])) {
                    return 'LLL:' . $registry[RegisterLanguageFileViewHelper::REGISTRY_KEY][$alias] . ':' . $id;
                }
            }
        }

        $path = 'LLL:EXT:' . GeneralUtility::camelCaseToLowerCaseUnderscored($request->getControllerExtensionName()) . '/Resources/Private/Language/';

        if (ContextUtility::isFrontend()) {
            $path .= 'Frontend';
        } else {
            $path .= 'Backend';
        }

        // Controller name may consist of several parts, e.g. Backend\Module.
        $controllerName = explode('\\', $request->getControllerName());

        // Remove Backend from array to avoid duplicate folder name in path.
        if (1 < count($controllerName) && 'Backend' === $controllerName[0]) {
            array_shift($controllerName);
        }

        return $path . '/' . implode('/', $controllerName) . '/' . $request->getControllerActionName() . '.xlf:' . $id;
    }
    
    /**
     * @param string $value
     * @param array  $translateArguments
     * @param bool   $recursive
     *
     * @return void
     */
    private static function formatVolatileString(
        string &$value,
        array $translateArguments,
        bool $recursive = false
    ): void {
        try {
            $value = vsprintf($value, $translateArguments);
        } catch (ValueError $error) {
            if ($recursive) {
                throw $error;
            }

            $value = str_replace('%', '%%', $value);
            self::formatVolatileString($value, $translateArguments, true);
        }
    }
}
