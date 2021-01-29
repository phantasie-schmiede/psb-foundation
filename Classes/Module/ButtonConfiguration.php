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

namespace PSB\PsbFoundation\Module;

use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\Buttons\InputButton;
use TYPO3\CMS\Backend\Template\Components\Buttons\LinkButton;

/**
 * Class ButtonConfiguration
 *
 * This class is a property container for action buttons in backend modules.
 *
 * @package PSB\PsbFoundation\Module
 * @see     \PSB\PsbFoundation\Controller\Backend\AbstractModuleController
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
    protected string $action = '';

    /**
     * @var array
     */
    protected array $additionalParams = [];

    /**
     * @var int
     */
    protected int $buttonGroup = 1;

    /**
     * @var string
     */
    protected string $controller = '';

    /**
     * @var string
     */
    protected string $form = '';

    /**
     * @var string
     */
    protected string $iconIdentifier = '';

    /**
     * @var string
     */
    protected string $name = '';

    /**
     * @var string
     */
    protected string $position = ButtonBar::BUTTON_POSITION_LEFT;

    /**
     * @var bool
     */
    protected bool $showLabel = true;

    /**
     * @var string
     */
    protected string $title = '';

    /**
     * @var string
     */
    protected string $type = '';

    /**
     * @var string
     */
    protected string $value = '';

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
