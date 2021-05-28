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

use PSB\PsbFoundation\Traits\PropertyInjection\FlashMessageServiceTrait;
use PSB\PsbFoundation\Traits\PropertyInjection\FrontendUserServiceTrait;
use PSB\PsbFoundation\Traits\PropertyInjection\LocalizationServiceTrait;
use PSB\PsbFoundation\Traits\PropertyInjection\UriBuilderTrait;
use PSB\PsbFoundation\Utility\Configuration\FilePathUtility;
use PSB\PsbFoundation\Utility\ValidationUtility;
use RuntimeException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class ObjectService
 *
 * @package PSB\PsbFoundation\Service
 */
class ValidationService
{
    use FlashMessageServiceTrait, FrontendUserServiceTrait, LocalizationServiceTrait, UriBuilderTrait;

    public const MODES = [
        'REDIRECT'        => 'redirect',
        'THROW_EXCEPTION' => 'throwException',
    ];

    /**
     * @param string $mode
     *
     * @throws AspectNotFoundException
     * @throws AspectPropertyNotFoundException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws PropagateResponseException
     */
    public function requiresFrontendUser(string $mode = self::MODES['REDIRECT']): void
    {
        ValidationUtility::checkValueAgainstConstant(self::MODES, $mode);
        ValidationUtility::requiresFrontendContext();

        if (null === $this->frontendUserService->getCurrentUser()) {
            switch ($mode) {
                case self::MODES['REDIRECT']:
                    $this->addFlashMessage();
                    throw new PropagateResponseException(new RedirectResponse($this->buildRedirectUri(), 401),
                        1621982088);
                case self::MODES['THROW_EXCEPTION']:
                    throw new RuntimeException(__CLASS__ . ': This method requires a logged in frontend user!',
                        1621981688);
            }
        }
    }

    /**
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    private function addFlashMessage(): void
    {
        $languageFilePath = FilePathUtility::getLanguageFilePath();
        $message = GeneralUtility::makeInstance(FlashMessage::class,
            $this->localizationService->translate($languageFilePath . 'flashMessage.redirect.bodytext'),
            $this->localizationService->translate($languageFilePath . 'flashMessage.redirect.header'),
            FlashMessage::ERROR,
            true
        );
        $this->flashMessageService->getMessageQueueByIdentifier('psbFoundation.general')->addMessage($message);
    }

    /**
     * @return string
     */
    private function buildRedirectUri(): string
    {
        $loginPage = $this->frontendUserService->getLoginPage();

        if (null === $loginPage) {
            $targetPageUid = $GLOBALS['TSFE']->rootLine[0]['uid'];
        } else {
            $targetPageUid = $loginPage->getUid();
        }

        return $this->uriBuilder->reset()
            ->setTargetPageUid($targetPageUid)
            ->buildFrontendUri();
    }
}
