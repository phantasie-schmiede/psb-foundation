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

namespace PSB\PsbFoundation\Utility;

use PSB\PsbFoundation\Service\GlobalVariableProviders\SiteConfigurationProvider;
use PSB\PsbFoundation\Service\GlobalVariableService;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ContextUtility
 *
 * @package PSB\PsbFoundation\Utility
 */
class ContextUtility
{
    public const DEFAULT_LANGUAGE_KEY = 'en';

    public static function getCurrentBackendLanguage(): string
    {
        ValidationUtility::requiresBackendContext();
        $language = $GLOBALS['BE_USER']->uc['lang'];

        if ('' === $language) {
            // Fallback to default language.
            return self::DEFAULT_LANGUAGE_KEY;
        }

        return $language;
    }

    /**
     * @return SiteLanguage
     * @throws AspectNotFoundException
     */
    public static function getCurrentFrontendLanguage(): SiteLanguage
    {
        ValidationUtility::requiresFrontendContext();

        /** @var Site $siteConfiguration */
        $siteConfiguration = GlobalVariableService::get(SiteConfigurationProvider::KEY);
        $context = GeneralUtility::makeInstance(Context::class);

        return $siteConfiguration->getLanguageById($context->getPropertyFromAspect('language', 'id'));
    }

    /**
     * @return string
     * @throws AspectNotFoundException
     */
    public static function getCurrentLocale(): string
    {
        if (self::isBackend()) {
            return self::getCurrentBackendLanguage();
        }

        if (self::isFrontend()) {
            return self::getCurrentFrontendLanguage()->getLocale();
        }

        return self::DEFAULT_LANGUAGE_KEY;
    }

    /**
     * @return bool
     */
    public static function isBackend(): bool
    {
        return ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend();
    }

    /**
     * @return bool
     */
    public static function isBootProcessRunning(): bool
    {
        return !GeneralUtility::getContainer()->get('boot.state')->done;
    }

    /**
     * @return bool
     */
    public static function isFrontend(): bool
    {
        return ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend();
    }

    /**
     * @return bool
     */
    public static function isTypoScriptAvailable(): bool
    {
        if (null !== $GLOBALS['TSFE'] && isset($GLOBALS['TSFE']) && self::isFrontend()) {
            return true;
        }

        return !self::isBootProcessRunning();
    }
}
