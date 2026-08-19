<?php

declare(strict_types=1);

/*
 * This file is part of PSBits Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\Foundation\Controller\Backend;

use JsonException;
use PSBits\Foundation\Attribute\ModuleAction;
use PSBits\Foundation\Service\RegisteredIconService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;

#[AsController]
class RegisteredIconsController extends AbstractModuleController
{
    public function __construct(
        protected readonly RegisteredIconService $registeredIconService,
        ModuleTemplateFactory                    $moduleTemplateFactory,
    ) {
        parent::__construct($moduleTemplateFactory);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    #[ModuleAction(default: true)]
    public function overviewAction(): ResponseInterface
    {
        $registeredIcons = $this->registeredIconService->getRegisteredIcons();
        $duplicateCount  = count(
            array_filter(
                $registeredIcons,
                static fn(array $icon): bool => true === $icon['hasDuplicateIdentifier']
            )
        );

        $this->moduleTemplate->assignMultiple([
            'duplicateCount'          => $duplicateCount,
            'registeredIcons'         => $registeredIcons,
            'registeredIconsByBundle' => $this->registeredIconService->getRegisteredIconsGroupedByExtension(),
            'totalCount'              => count($registeredIcons),
        ]);

        return $this->htmlResponse();
    }
}
