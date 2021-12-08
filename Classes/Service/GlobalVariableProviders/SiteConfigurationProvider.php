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
use PSB\PsbFoundation\Utility\ValidationUtility;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * Class SiteConfigurationProvider
 *
 * @package PSB\PsbFoundation\Service\GlobalVariableProviders
 */
class SiteConfigurationProvider extends AbstractProvider
{
    use SiteFinderTrait;

    /**
     * @return Site
     * @throws SiteNotFoundException
     */
    public function getGlobalVariables(): Site
    {
        ValidationUtility::requiresFrontendContext();
        ValidationUtility::requiresTypoScriptLoaded();

        return $this->siteFinder->getSiteByPageId((int)$GLOBALS['TSFE']->id);
    }
}
