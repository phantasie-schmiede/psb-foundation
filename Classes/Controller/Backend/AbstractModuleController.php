<?php
declare(strict_types=1);

namespace PSB\PsbFoundation\Controller\Backend;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use InvalidArgumentException;
use PSB\PsbFoundation\Module\ButtonConfiguration;
use PSB\PsbFoundation\Utilities\VariableUtility;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use TYPO3\CMS\Backend\Template\Components\Buttons\InputButton;
use TYPO3\CMS\Backend\Template\Components\Buttons\LinkButton;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use function count;
use function in_array;

/**
 * Class AbstractModuleController
 *
 * Extend this class with your backend module controller to add features like the bookmark icon, action menu and
 * buttons.
 *
 * @package PSB\PsbFoundation\Controller\Backend
 */
abstract class AbstractModuleController extends ActionController
{
    protected const HEADER_COMPONENTS = [
        'ACTION_BUTTONS'  => 'actionButtons',
        'ACTION_MENU'     => 'actionMenu',
        'SHORTCUT_BUTTON' => 'shortcutButton',
    ];

    protected const HEADER_SETTINGS = [
        'TEMPLATE_ACTIONS' => 'templateActions',
    ];

    /**
     * Backend Template Container
     *
     * @var string
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    /**
     * Configuration that defines the rendering of the module header
     *
     * The first level keys of this array correspond to the values of self::HEADER_COMPONENTS. The associated keys are:
     * actionButtons => buttons, render
     * actionMenu => items, render
     * shortcutButton => bookmarkLabel, render
     *
     * @var array
     */
    protected $headerConfiguration;

    /**
     * @var BackendTemplateView
     */
    protected $view;

    /**
     * All header components are activated by default. This can be overridden in your module's constructor. Simply call
     * setHeaderComponents() by yourself and pass the desired arguments.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setHeaderComponents();
    }

    /**
     * @param ButtonConfiguration $buttonConfiguration
     */
    public function addActionButton(ButtonConfiguration $buttonConfiguration): void
    {
        $this->headerConfiguration[self::HEADER_COMPONENTS['ACTION_BUTTONS']]['buttons'][] = $buttonConfiguration;
    }

    /**
     * This controller needs to know, after which action a view has to be rendered. You can register those actions with
     * this method.
     *
     * @param string $templateAction
     */
    public function addTemplateAction(string $templateAction): void
    {
        $this->headerConfiguration[self::HEADER_SETTINGS['TEMPLATE_ACTIONS']][] = $templateAction;
    }

    /**
     * @return array
     */
    public function getActionButtons(): array
    {
        return $this->headerConfiguration[self::HEADER_COMPONENTS['ACTION_BUTTONS']]['buttons'] ?? [];
    }

    /**
     * This getter includes a fallback for a default label if none is given.
     *
     * @return string|null
     */
    public function getBookmarkLabel(): ?string
    {
        return $this->headerConfiguration[self::HEADER_COMPONENTS['SHORTCUT_BUTTON']]['bookmarkLabel'] ?? $this->buildBookmarkLabel();
    }

    /**
     * @return array
     */
    public function getHeaderConfiguration(): array
    {
        return $this->headerConfiguration;
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function getMenuItems(): array
    {
        return $this->headerConfiguration[self::HEADER_COMPONENTS['ACTION_MENU']]['items'] ?? $this->buildMenuItems();
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function getTemplateActions(): array
    {
        return $this->headerConfiguration[self::HEADER_SETTINGS['TEMPLATE_ACTIONS']] ?? $this->buildTemplateActions();
    }

    /**
     * This method accepts an array of objects that have to be instances of
     * \PSB\PsbFoundation\Module\ButtonConfiguration.
     *
     * @param array $buttonConfigurations Array of ButtonConfiguration-objects
     *
     * @throws InvalidArgumentException
     * @see \PSB\PsbFoundation\Module\ButtonConfiguration
     */
    public function setActionButtons(array $buttonConfigurations): void
    {
        foreach ($buttonConfigurations as $buttonConfiguration) {
            if (!$buttonConfiguration instanceof ButtonConfiguration) {
                throw new InvalidArgumentException ('AbstractModuleController: ALL items of the array passed to setActionButtons must be an instance of ButtonConfiguration!',
                    1543411968);
            }
        }

        $this->headerConfiguration[self::HEADER_COMPONENTS['ACTION_BUTTONS']]['buttons'] = $buttonConfigurations;
    }

    /**
     * @param string $shortcutName
     */
    public function setBookmarkLabel(string $shortcutName): void
    {
        $this->headerConfiguration[self::HEADER_COMPONENTS['SHORTCUT_BUTTON']]['bookmarkLabel'] = $shortcutName;
    }

    /**
     * This methods allows to define the components which should be rendered.
     *
     * @param bool $renderActionButtons
     * @param bool $renderActionMenu
     * @param bool $renderShortCutButton
     */
    public function setHeaderComponents(
        bool $renderActionButtons = true,
        bool $renderActionMenu = true,
        bool $renderShortCutButton = true
    ): void {
        $this->headerConfiguration[self::HEADER_COMPONENTS['ACTION_BUTTONS']]['render'] = $renderActionButtons;
        $this->headerConfiguration[self::HEADER_COMPONENTS['ACTION_MENU']]['render'] = $renderActionMenu;
        $this->headerConfiguration[self::HEADER_COMPONENTS['SHORTCUT_BUTTON']]['render'] = $renderShortCutButton;
    }

    /**
     * @param array $menuItems Array of associative arrays which have to contain these keys: action, controller and
     *                         label
     */
    public function setMenuItems(array $menuItems): void
    {
        $this->headerConfiguration[self::HEADER_COMPONENTS['ACTION_MENU']]['items'] = $menuItems;
    }

    /**
     * @param array $templateActions
     */
    public function setTemplateActions(array $templateActions): void
    {
        $this->headerConfiguration[self::HEADER_SETTINGS['TEMPLATE_ACTIONS']] = $templateActions;
    }

    /**
     * @param string $component Use constant self::HEADER_COMPONENTS for this argument
     *
     * @return bool
     */
    public function shallBeRendered(string $component): bool
    {
        return $this->headerConfiguration[$component]['render'];
    }

    /**
     * @param ViewInterface $view
     *
     * @throws ReflectionException
     */
    protected function initializeView(ViewInterface $view): void
    {
        parent::initializeView($view);

        if (!in_array($this->request->getControllerActionName(), $this->getTemplateActions(), true)) {
            return;
        }

        if ($this->shallBeRendered(self::HEADER_COMPONENTS['ACTION_BUTTONS'])) {
            $this->generateActionButtons();

            if ($view instanceof BackendTemplateView) {
                $view->getModuleTemplate()->getPageRenderer()->addRequireJsConfiguration([
                    'paths' => [
                        'fixHeaderDocInputButtons' => '/typo3conf/ext/psb_foundation/Resources/Public/Scripts/Backend/fixHeaderDocInputButtons',
                    ],
                ]);
                $view->getModuleTemplate()->getPageRenderer()->loadRequireJsModule('fixHeaderDocInputButtons');
            }
        }

        if ($this->shallBeRendered(self::HEADER_COMPONENTS['ACTION_MENU'])) {
            $this->generateMenu();
        }

        if ($this->shallBeRendered(self::HEADER_COMPONENTS['SHORTCUT_BUTTON'])) {
            $this->generateShortcutButton();
        }

        $this->view->getModuleTemplate()->setFlashMessageQueue($this->controllerContext->getFlashMessageQueue());

        // @TODO: Test!
        // @TODO: Can this be moved to a ViewHelper?
        if ($view instanceof BackendTemplateView) {
            $view->getModuleTemplate()->getPageRenderer()->addRequireJsConfiguration([
                'paths' => [
                    'datetimepicker' => '/typo3/sysext/backend/Resources/Public/JavaScript/DateTimePicker',
                ],
            ]);
            $view->getModuleTemplate()->getPageRenderer()->loadRequireJsModule('DateTimePicker');
        }
    }

    /**
     * Fallback method if no bookmark label has been given. At first, default label path is checked:
     * EXT:your_extension/Resources/Private/Language/Backend/Modules/shortControllerName.xlf:bookmarkLabel
     * If no label is found, module label is used (if defined at default position), and if there is an action menu, the
     * current action will be appended in brackets.
     *
     * @return string|null
     */
    private function buildBookmarkLabel(): ?string
    {
        $actionName = $this->request->getControllerActionName();
        $languageFile = $this->getDefaultLanguageFile();
        $bookmarkLabel = LocalizationUtility::translate($languageFile.':bookmarkLabel.'.$actionName);

        if (null === $bookmarkLabel) {
            $bookmarkLabel = LocalizationUtility::translate($languageFile.':bookmarkLabel')
                ?? LocalizationUtility::translate($languageFile.':mlang_tabs_tab');

            if ($this->shallBeRendered(self::HEADER_COMPONENTS['ACTION_MENU'])) {
                $bookmarkLabel .= ' ('.ucfirst($actionName).')';
            }
        }

        $this->setBookmarkLabel($bookmarkLabel ?? '');

        return $bookmarkLabel;
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    private function buildMenuItems(): array
    {
        $items = [];
        $actions = $this->getTemplateActions();

        foreach ($actions as $action) {
            $items[] = [
                'action'     => $action,
                'controller' => $this->request->getControllerName(),
                'label'      => LocalizationUtility::translate($this->getDefaultLanguageFile().':menu.'.$action) ?? $action,
            ];
        }

        $this->setMenuItems($items);

        return $items;
    }

    /**
     * Fallback method if no templateActions were registered. All methods whose name ends with "Action" are registered
     * automatically (except for addTemplateAction).
     *
     * @return array
     * @throws ReflectionException
     */
    private function buildTemplateActions(): array
    {
        $actions = (new ReflectionClass($this))->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($actions as $action) {
            $action = preg_replace('/Action$/', '', $action->getName(), 1, $count);

            if (1 === $count && 'addTemplate' !== $action) {
                $this->addTemplateAction($action);
            }
        }

        return $this->headerConfiguration[self::HEADER_SETTINGS['TEMPLATE_ACTIONS']];
    }

    private function generateActionButtons(): void
    {
        $buttonBar = $this->view->getModuleTemplate()->getDocHeaderComponent()->getButtonBar();
        $actionButtons = $this->getActionButtons();

        /** @var ButtonConfiguration $buttonConfiguration */
        foreach ($actionButtons as $buttonConfiguration) {
            $icon = $this->view->getModuleTemplate()
                ->getIconFactory()
                ->getIcon($buttonConfiguration->getIconIdentifier(), Icon::SIZE_SMALL);
            $button = $buttonBar->makeButton($buttonConfiguration->getType());

            if ($button instanceof LinkButton) {
                $action = $buttonConfiguration->getAction();
                $link = $this->getHref($action,
                    $buttonConfiguration->getController() ?? $this->request->getControllerName());
                $title = $buttonConfiguration->getTitle() ?? LocalizationUtility::translate($this->getDefaultLanguageFile().':button.'.$action) ?? '';
                $button->setHref($link);
            }

            if ($button instanceof InputButton) {
                $title = $buttonConfiguration->getTitle() ?? LocalizationUtility::translate($this->getDefaultLanguageFile().':button.'.$buttonConfiguration->getForm()) ?? '';
                $button->setForm($buttonConfiguration->getForm())
                    ->setName($buttonConfiguration->getName())
                    ->setValue($buttonConfiguration->getValue());
            }

            $button->setIcon($icon)
                ->setShowLabelText($buttonConfiguration->isShowLabel())
                ->setTitle($title ?? '');
            $buttonBar->addButton($button, $buttonConfiguration->getPosition(),
                $buttonConfiguration->getButtonGroup());
        }
    }

    /**
     * @throws ReflectionException
     */
    private function generateMenu(): void
    {
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($this->request);

        $menu = $this->view->getModuleTemplate()->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $identifier = (new ReflectionClass($this))->getShortName().'Menu';
        $menu->setIdentifier($identifier);
        $menuItems = $this->getMenuItems();

        foreach ($menuItems as $menuItemConfig) {
            if ($this->request->getControllerName() === $menuItemConfig['controller']) {
                $isActive = $this->request->getControllerActionName() === $menuItemConfig['action'];
            } else {
                $isActive = false;
            }

            $menuItem = $menu->makeMenuItem()
                ->setTitle($menuItemConfig['label'])
                ->setHref($this->getHref($menuItemConfig['action'], $menuItemConfig['controller']))
                ->setActive($isActive);
            $menu->addMenuItem($menuItem);
        }

        $this->view->getModuleTemplate()->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
    }

    /**
     * @TODO: Action is not saved correctly. Shortcut always calls the default action. ($getVars should contain action)
     */
    private function generateShortcutButton(): void
    {
        $buttonBar = $this->view->getModuleTemplate()->getDocHeaderComponent()->getButtonBar();
        $extensionName = $this->request->getControllerExtensionName();
        $getVars = $this->request->getArguments();
        $moduleName = $this->request->getPluginName();

        if (0 === count($getVars)) {
            $modulePrefix = strtolower('tx_'.$extensionName.'_'.$moduleName);
            $getVars = ['id', 'M', $modulePrefix];
        }

        $shortcutButton = $buttonBar->makeShortcutButton()
            ->setDisplayName($this->getBookmarkLabel())
            ->setGetVariables($getVars)
            ->setModuleName($moduleName);
        $buttonBar->addButton($shortcutButton);
    }

    /**
     * @return string
     */
    private function getDefaultLanguageFile(): string
    {
        $fileName = lcfirst(VariableUtility::convertClassNameToControllerName(get_class($this))).'.xlf';

        return 'LLL:EXT:'.$this->request->getControllerExtensionKey().'/Resources/Private/Language/Backend/Modules/'.$fileName;
    }

    /**
     * Get URI of backend action.
     *
     * @param string $action
     * @param string $controller
     * @param array  $parameters
     *
     * @return string
     */
    private function getHref($action, $controller, $parameters = []): string
    {
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($this->request);

        return $uriBuilder->reset()->uriFor($action, $parameters, $controller);
    }
}
