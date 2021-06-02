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

use PSB\PsbFoundation\Traits\PropertyInjection\FrontendUserServiceTrait;
use PSB\PsbFoundation\Utility\ValidationUtility;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;

/**
 * Class FrontendUserProvider
 *
 * @package PSB\PsbFoundation\Service\GlobalVariableProviders
 */
class FrontendUserProvider extends AbstractProvider
{
    use FrontendUserServiceTrait;

    public const KEY = 'psbFoundation-frontendUser';

    /**
     * @return array
     * @throws AspectPropertyNotFoundException
     * @throws AspectNotFoundException
     */
    public function getGlobalVariables(): array
    {
        ValidationUtility::requiresFrontendContext();
        ValidationUtility::requiresTypoScriptLoaded();

        return [
            'loginPage' => $this->frontendUserService->getLoginPage(),
            'sysFolder' => $this->frontendUserService->getSysFolder(),
            'user'      => $this->frontendUserService->getCurrentUser(),
        ];
    }
}
