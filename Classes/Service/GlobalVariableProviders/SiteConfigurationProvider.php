<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Service\GlobalVariableProviders;

use PSB\PsbFoundation\Traits\PropertyInjection\SiteFinderTrait;
use PSB\PsbFoundation\Utility\ValidationUtility;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws SiteNotFoundException
     */
    public function getGlobalVariables(): Site
    {
        ValidationUtility::requiresFrontendContext();
        ValidationUtility::requiresTypoScriptLoaded();

        return $this->siteFinder->getSiteByPageId((int)$GLOBALS['TSFE']->id);
    }
}
