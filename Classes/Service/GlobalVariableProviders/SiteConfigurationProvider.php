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

namespace PSB\PsbFoundation\Service\GlobalVariableProviders;

use PSB\PsbFoundation\Traits\PropertyInjection\SiteFinderTrait;
use PSB\PsbFoundation\Utility\ContextUtility;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;

/**
 * Class SiteConfigurationProvider
 *
 * @package PSB\PsbFoundation\Service\GlobalVariableProviders
 */
class SiteConfigurationProvider extends AbstractProvider
{
    use SiteFinderTrait;

    public const KEY = 'siteConfiguration';

    /**
     * @return bool|null
     */
    public static function isAvailable(): ?bool
    {
        if (!ContextUtility::isFrontend()) {
            return false;
        }

        return ContextUtility::isTypoScriptAvailable() ? null : true;
    }

    /**
     * @return array
     * @throws SiteNotFoundException
     */
    public function getGlobalVariables(): array
    {
        $site = $this->siteFinder->getSiteByPageId((int)$GLOBALS['TSFE']->id);

        return [self::KEY => $site];
    }
}
