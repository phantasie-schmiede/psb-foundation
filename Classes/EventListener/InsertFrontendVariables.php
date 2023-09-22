<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\EventListener;

use JsonException;
use PSB\PsbFoundation\Service\FrontendVariableService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Frontend\Event\AfterCacheableContentIsGeneratedEvent;

/**
 * Class InsertFrontendVariables
 *
 * @package PSB\PsbFoundation\EventListener
 */
class InsertFrontendVariables
{
    public function __construct(
        protected readonly FrontendVariableService $frontendVariableService,
    ) {
    }

    /**
     * @param AfterCacheableContentIsGeneratedEvent $event
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(AfterCacheableContentIsGeneratedEvent $event): void
    {
        if (!$this->frontendVariableService->isEnabled()) {
            return;
        }

        $frontendVariables = $this->frontendVariableService->getVariablesForRootline(
            $event->getRequest()
                ->getAttribute('routing')
                ->getPageId()
        );
        $markerReplacements = $this->frontendVariableService->getReplacementArray($frontendVariables);
        $event->getController()->content = str_replace(
            array_keys($markerReplacements),
            array_values($markerReplacements),
            $event->getController()->content
        );
    }
}
