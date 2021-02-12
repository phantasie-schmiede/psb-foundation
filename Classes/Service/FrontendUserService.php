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

namespace PSB\PsbFoundation\Service;

use PSB\PsbFoundation\Traits\Properties\FrontendUserRepositoryTrait;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class FrontendUserService
 *
 * @package PSB\PsbFoundation\Service
 */
class FrontendUserService
{
    use FrontendUserRepositoryTrait;

    /**
     * @return FrontendUser|null
     * @throws AspectPropertyNotFoundException
     * @throws Exception
     */
    public function getCurrentUser(): ?FrontendUser
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->frontendUserRepository->findByUid($this->getCurrentUserId());
    }

    /**
     * @return int
     * @throws AspectNotFoundException
     * @throws AspectPropertyNotFoundException
     */
    public function getCurrentUserId(): int
    {
        $context = GeneralUtility::makeInstance(Context::class);

        return $context->getAspect('frontend.user')->get('id');
    }
}
