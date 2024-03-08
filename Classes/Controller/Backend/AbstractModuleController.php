<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class AbstractModuleController
 *
 * @package PSB\PsbFoundation\Controller\Backend
 */
abstract class AbstractModuleController extends ActionController
{
    protected ModuleTemplate $moduleTemplate;

    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
    ) {
    }

    /**
     * Returns a response object with the rendered module template.
     */
    protected function htmlResponse(string $html = null): ResponseInterface
    {
        return $this->moduleTemplate->renderResponse();
    }

    protected function initializeAction(): void
    {
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
    }
}
