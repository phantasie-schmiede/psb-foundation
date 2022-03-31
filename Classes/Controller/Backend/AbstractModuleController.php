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
 * Extend this class with your backend module controller to simplify backend rendering. Just call the render-function
 * at the end of each of your actions which have a corresponding fluid-template.
 *
 * @package PSB\PsbFoundation\Controller\Backend
 */
abstract class AbstractModuleController extends ActionController
{
    /**
     * @param ModuleTemplate $moduleTemplate
     */
    protected ModuleTemplate $moduleTemplate;

    /**
     * @var ModuleTemplateFactory
     */
    protected ModuleTemplateFactory $moduleTemplateFactory;

    /**
     * @param ModuleTemplateFactory $moduleTemplateFactory
     */
    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
    }

    /**
     * @return void
     */
    protected function initializeAction(): void
    {
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
    }

    /**
     * @return ResponseInterface
     */
    protected function render(): ResponseInterface
    {
        $this->moduleTemplate->setContent($this->view->render());

        return $this->htmlResponse($this->moduleTemplate->renderContent());
    }
}
