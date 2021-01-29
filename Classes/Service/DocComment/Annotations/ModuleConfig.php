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

namespace PSB\PsbFoundation\Service\DocComment\Annotations;

/**
 * Class ModuleConfig
 *
 * Use this annotation for a module controller class.
 *
 * @Annotation
 * @package PSB\PsbFoundation\Service\DocComment\Annotations
 */
class ModuleConfig extends AbstractAnnotation
{
    /**
     * @var string
     */
    protected string $access = '';

    /**
     * @var string
     */
    protected string $icon = '';

    /**
     * @var string
     */
    protected string $iconIdentifier = '';

    /**
     * @var string|null
     */
    protected ?string $labels = null;

    /**
     * @var string
     */
    protected string $mainModuleName = '';

    /**
     * @var string
     */
    protected string $navigationComponentId = '';

    /**
     * @var string
     */
    protected string $position = '';

    /**
     * @return string
     */
    public function getAccess(): string
    {
        return $this->access;
    }

    /**
     * @param string $access
     */
    public function setAccess(string $access): void
    {
        $this->access = $access;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     */
    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
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
     */
    public function setIconIdentifier(string $iconIdentifier): void
    {
        $this->iconIdentifier = $iconIdentifier;
    }

    /**
     * @return string|null
     */
    public function getLabels(): ?string
    {
        return $this->labels;
    }

    /**
     * @param string|null $labels
     */
    public function setLabels(?string $labels): void
    {
        $this->labels = $labels;
    }

    /**
     * @return string
     */
    public function getMainModuleName(): string
    {
        return $this->mainModuleName;
    }

    /**
     * @param string $mainModuleName
     */
    public function setMainModuleName(string $mainModuleName): void
    {
        $this->mainModuleName = $mainModuleName;
    }

    /**
     * @return string
     */
    public function getNavigationComponentId(): string
    {
        return $this->navigationComponentId;
    }

    /**
     * @param string $navigationComponentId
     */
    public function setNavigationComponentId(string $navigationComponentId): void
    {
        $this->navigationComponentId = $navigationComponentId;
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
     */
    public function setPosition(string $position): void
    {
        $this->position = $position;
    }
}
