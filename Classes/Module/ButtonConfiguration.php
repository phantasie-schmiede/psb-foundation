<?php
declare(strict_types=1);

namespace PSB\PsbFoundation\Module;

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

use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\Buttons\InputButton;
use TYPO3\CMS\Backend\Template\Components\Buttons\LinkButton;

/**
 * Class ButtonConfiguration
 *
 * This class is a property container for action buttons in backend modules.
 *
 * @package PSB\PsbFoundation\Module
 * @see \PSB\PsbFoundation\Controller\Backend\AbstractModuleController
 */
class ButtonConfiguration
{
    public const BUTTON_TYPES = [
        'INPUT_BUTTON' => InputButton::class,
        'LINK_BUTTON'  => LinkButton::class,
    ];

    /**
     * @var string
     */
    protected $action;

    /**
     * @var array
     */
    protected $additionalParams;

    /**
     * @var int
     */
    protected $buttonGroup = 1;

    /**
     * @var string
     */
    protected $controller;

    /**
     * @var string
     */
    protected $form;

    /**
     * @var string
     */
    protected $iconIdentifier;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $position = ButtonBar::BUTTON_POSITION_LEFT;

    /**
     * @var bool
     */
    protected $showLabel = true;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $value;

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     *
     * @return ButtonConfiguration
     */
    public function setAction(string $action): ButtonConfiguration
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return array
     */
    public function getAdditionalParams(): array
    {
        return $this->additionalParams;
    }

    /**
     * @param array $additionalParams
     *
     * @return ButtonConfiguration
     */
    public function setAdditionalParams(array $additionalParams): ButtonConfiguration
    {
        $this->additionalParams = $additionalParams;

        return $this;
    }

    /**
     * @return int
     */
    public function getButtonGroup(): int
    {
        return $this->buttonGroup;
    }

    /**
     * @param int $buttonGroup
     *
     * @return ButtonConfiguration
     */
    public function setButtonGroup(int $buttonGroup): ButtonConfiguration
    {
        $this->buttonGroup = $buttonGroup;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getController(): ?string
    {
        return $this->controller;
    }

    /**
     * @param string $controller
     *
     * @return ButtonConfiguration
     */
    public function setController(string $controller): ButtonConfiguration
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @return string
     */
    public function getForm(): string
    {
        return $this->form;
    }

    /**
     * @param string $form
     *
     * @return ButtonConfiguration
     */
    public function setForm(string $form): ButtonConfiguration
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return string
     */
    public function getIconIdentifier(): string
    {
        return $this->iconIdentifier;
    }

    /**
     * @param string $iconIdentifier
     *
     * @return ButtonConfiguration
     */
    public function setIconIdentifier(string $iconIdentifier): ButtonConfiguration
    {
        $this->iconIdentifier = $iconIdentifier;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return ButtonConfiguration
     */
    public function setName(string $name): ButtonConfiguration
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * @param string $position
     *
     * @return ButtonConfiguration
     */
    public function setPosition(string $position): ButtonConfiguration
    {
        if (ButtonBar::BUTTON_POSITION_LEFT === $position) {
            $this->position = ButtonBar::BUTTON_POSITION_LEFT;
        } else {
            $this->position = ButtonBar::BUTTON_POSITION_RIGHT;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isShowLabel(): bool
    {
        return $this->showLabel;
    }

    /**
     * @param bool $showLabel
     *
     * @return ButtonConfiguration
     */
    public function setShowLabel(bool $showLabel): ButtonConfiguration
    {
        $this->showLabel = $showLabel;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return ButtonConfiguration
     */
    public function setTitle(string $title): ButtonConfiguration
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type Use constant BUTTON_TYPES for this argument.
     *
     * @return ButtonConfiguration
     */
    public function setType(string $type): ButtonConfiguration
    {
        if (self::BUTTON_TYPES['INPUT_BUTTON'] === $type) {
            $this->type = self::BUTTON_TYPES['INPUT_BUTTON'];
        } else {
            $this->type = self::BUTTON_TYPES['LINK_BUTTON'];
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return ButtonConfiguration
     */
    public function setValue(string $value): ButtonConfiguration
    {
        $this->value = $value;

        return $this;
    }
}
