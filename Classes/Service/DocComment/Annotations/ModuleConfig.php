<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Service\DocComment\Annotations;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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
    protected string $access;

    /**
     * @var string
     */
    protected string $icon;

    /**
     * @var string
     */
    protected string $iconIdentifier;

    /**
     * @var string
     */
    protected string $labels;

    /**
     * @var string
     */
    protected string $mainModuleName;

    /**
     * @var string
     */
    protected string $navigationComponentId;

    /**
     * @var string
     */
    protected string $position;

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
     * @return string
     */
    public function getLabels(): string
    {
        return $this->labels;
    }

    /**
     * @param string $labels
     */
    public function setLabels(string $labels): void
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
