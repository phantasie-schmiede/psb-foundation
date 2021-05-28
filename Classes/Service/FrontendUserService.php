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

use PSB\PsbFoundation\Domain\Model\Typo3\Page;
use PSB\PsbFoundation\Domain\Model\Typo3\TtContent;
use PSB\PsbFoundation\Traits\PropertyInjection\FrontendUserRepositoryTrait;
use PSB\PsbFoundation\Traits\PropertyInjection\PageRepositoryTrait;
use PSB\PsbFoundation\Traits\PropertyInjection\TtContentRepositoryTrait;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;

/**
 * Class FrontendUserService
 *
 * @package PSB\PsbFoundation\Service
 */
class FrontendUserService
{
    use FrontendUserRepositoryTrait, PageRepositoryTrait, TtContentRepositoryTrait;

    /**
     * @return FrontendUser|null
     * @throws AspectNotFoundException
     * @throws AspectPropertyNotFoundException
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

    /**
     * @return Page|null
     */
    public function getLoginPage(): ?Page
    {
        /** @var TtContent|null $plugin */
        $plugin = $this->ttContentRepository->findByCType('felogin_login')->getFirst();

        if (null !== $plugin) {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return $this->pageRepository->findByUid($plugin->getPid());
        }

        return null;
    }

    /**
     * @return Page|null
     */
    public function getSysFolder(): ?Page
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->pageRepository->findByModule('fe_users')->getFirst();
    }
}
