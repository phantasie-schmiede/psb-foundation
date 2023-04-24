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
use JsonException;
use PSB\PsbFoundation\Service\LocalizationService;
use PSB\PsbFoundation\Utility\ContextUtility;
use PSB\PsbFoundation\ViewHelpers\Translation\RegisterLanguageFileViewHelper;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Mvc\RequestInterface as ExtbaseRequestInterface;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use function count;

/**
 * Class TranslateViewHelper
 *
 * Overwrites the core ViewHelper in order to use \PSB\PsbFoundation\Service\LocalizationService which is able to log
 * missing language labels.
 *
 * @package PSB\PsbFoundation\ViewHelpers
 */
class TranslateViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    public const MARKER_AFTER  = '}';
    public const MARKER_BEFORE = '{';

    /**
     * Output is escaped already. We must not escape children, to avoid double encoding.
     *
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * Return array element by key.
     *
     * @param array                     $arguments
     * @param Closure                   $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string|null
     * @throws ContainerExceptionInterface
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public static function renderStatic(
        array                     $arguments,
        Closure                   $renderChildrenClosure,
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
            throw new Exception('An argument "key" or "id" has to be provided', 1682312266);
        }

        $request = null;

        if ($renderingContext instanceof RenderingContext) {
            $request = $renderingContext->getRequest();
        }

        if (null === $extensionName && $request instanceof RequestInterface && !str_starts_with($id, 'LLL:')) {
            $extensionName = $request->getControllerExtensionName();
            $id = self::buildId($id, $renderingContext, $request);
        }

        try {
            if ($request instanceof ExtbaseRequestInterface) {
                $value = static::translate($id, $extensionName, $translateArguments, $arguments['languageKey'],
                    $arguments['alternativeLanguageKeys']);
            } else {
                $value = static::getLanguageService($request)->sL($id);
                GeneralUtility::makeInstance(LocalizationService::class)->translationExists($id);
            }
        } catch (InvalidArgumentException) {
            $value = null;
        }

        if (null === $value) {
            $value = $default ?? $renderChildrenClosure() ?? '';

            if (null !== $value && !empty($translateArguments)) {
                $value = vsprintf($value, $translateArguments);
            }
        }

        if (!empty($translateArguments) && ArrayUtility::isAssociative($translateArguments)) {
            $markerReplacements = [];

            foreach ($translateArguments as $marker => $replacement) {
                $markerReplacements[self::MARKER_BEFORE . $marker . self::MARKER_AFTER] = $replacement;
            }

            $value = str_replace(array_keys($markerReplacements), array_values($markerReplacements), $value);
        }

        return $value;
    }

    /**
     * @param ServerRequestInterface|null $request
     *
     * @return LanguageService
     * @see \TYPO3\CMS\Fluid\ViewHelpers\TranslateViewHelper
     */
    protected static function getLanguageService(ServerRequestInterface $request = null): LanguageService
    {
        if (isset($GLOBALS['LANG'])) {
            return $GLOBALS['LANG'];
        }
        $languageServiceFactory = GeneralUtility::makeInstance(LanguageServiceFactory::class);
        if ($request !== null && ApplicationType::fromRequest($request)->isFrontend()) {
            $GLOBALS['LANG'] = $languageServiceFactory->createFromSiteLanguage($request->getAttribute('language')
                ?? $request->getAttribute('site')->getDefaultLanguage());

            return $GLOBALS['LANG'];
        }
        $GLOBALS['LANG'] = $languageServiceFactory->createFromUserPreferences($GLOBALS['BE_USER'] ?? null);

        return $GLOBALS['LANG'];
    }

    /**
     * Wrapper call to static LocalizationService
     *
     * @param string      $id                      Translation Key
     * @param string|null $extensionName           UpperCamelCased extension key (for example BlogExample)
     * @param array|null  $arguments               Arguments to be replaced in the resulting string
     * @param string|null $languageKey             Language key to use for this translation
     * @param string[]    $alternativeLanguageKeys Alternative language keys if no translation does exist
     *
     * @return string|null
     * @throws ContainerExceptionInterface
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    protected static function translate(
        string $id,
        string $extensionName = null,
        array  $arguments = null,
        string $languageKey = null,
        array  $alternativeLanguageKeys = null,
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

    public function initializeArguments(): void
    {
        $this->registerArgument('alternativeLanguageKeys', 'array',
            'Alternative language keys if no translation does exist. Ignored in non-extbase context.');
        $this->registerArgument('arguments', 'array', 'Arguments to be replaced in the resulting string', false, []);
        $this->registerArgument('default', 'string',
            'If the given locallang key could not be found, this value is used. If this argument is not set, child nodes will be used to render the default');
        $this->registerArgument('extensionName', 'string', 'UpperCamelCased extension key (for example BlogExample)');
        $this->registerArgument('id', 'string', 'Translation ID. Same as key.');
        $this->registerArgument('key', 'string', 'Translation Key');
        $this->registerArgument('languageKey', 'string',
            'Language key ("dk" for example) or "default" to use. If empty, use current language. Ignored in non-extbase context.');
    }
}
