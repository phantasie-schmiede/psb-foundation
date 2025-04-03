<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Utility;

use PSB\PsbFoundation\Service\GlobalVariableProviders\SiteConfigurationProvider;
use PSB\PsbFoundation\Service\GlobalVariableService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use function is_array;

/**
 * Class ContextUtility
 *
 * @package PSB\PsbFoundation\Utility
 */
class ContextUtility
{
    public const DEFAULT_LANGUAGE_KEY = 'default';

    public static function getCurrentBackendLanguage(): string
    {
        ValidationUtility::requiresBackendContext();
        $language = (string)$GLOBALS['BE_USER']->user['lang'];

        if ('' === $language) {
            // Fallback to default language.
            return self::DEFAULT_LANGUAGE_KEY;
        }

        return $language;
    }

    /**
     * @throws AspectNotFoundException
     */
    public static function getCurrentFrontendLanguage(): SiteLanguage
    {
        ValidationUtility::requiresFrontendContext();

        /** @var Site $siteConfiguration */
        $siteConfiguration = GlobalVariableService::get(SiteConfigurationProvider::class);
        $context = GeneralUtility::makeInstance(Context::class);

        return $siteConfiguration->getLanguageById($context->getPropertyFromAspect('language', 'id'));
    }

    /**
     * @throws AspectNotFoundException
     */
    public static function getCurrentLocale(): string
    {
        if (self::isBackend()) {
            return self::getCurrentBackendLanguage();
        }

        if (self::isFrontend()) {
            return self::getCurrentFrontendLanguage()
                ->getLocale()
                ->getName();
        }

        return self::DEFAULT_LANGUAGE_KEY;
    }

    public static function getPluginSignatureFromRequest(Request $request): string
    {
        return strtolower('tx_' . $request->getControllerExtensionName() . '_' . $request->getPluginName());
    }

    public static function getRequest(): ?ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? null;
    }

    public static function isBackend(): bool
    {
        $request = self::getRequest();

        return null !== $request && ApplicationType::fromRequest($request)
                ->isBackend();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function isBootProcessRunning(): bool
    {
        return !GeneralUtility::getContainer()
            ->get('boot.state')->complete;
    }

    public static function isFrontend(): bool
    {
        $request = self::getRequest();

        return null !== $request && ApplicationType::fromRequest($request)
                ->isFrontend();
    }

    public static function isTypoScriptAvailable(): bool
    {
        try {
            $typoScript = self::getRequest()
                ?->getAttribute('frontend.typoscript')
                ?->getSetupArray();
        } catch (RuntimeException) {
            return false;
        }

        return is_array($typoScript);
    }
}
