<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Annotation;

/**
 * Class ModuleConfig
 *
 * Use this annotation for a module controller class.
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation
 */
class ModuleConfig extends AbstractAnnotation
{
    /**
     * @var string
     */
    protected string $access = 'group, user';

    /**
     * @var string|null
     */
    protected ?string $iconIdentifier = null;

    /**
     * @var string|null
     */
    protected ?string $labels = null;

    /**
     * @var string
     */
    protected string $mainModuleName = 'web';

    /**
     * @var string|null
     */
    protected ?string $navigationComponentId = null;

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
     * @return string|null
     */
    public function getIconIdentifier(): ?string
    {
        return $this->iconIdentifier;
    }

    /**
     * @param string|null $iconIdentifier
     */
    public function setIconIdentifier(?string $iconIdentifier): void
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
     * @return string|null
     */
    public function getNavigationComponentId(): ?string
    {
        return $this->navigationComponentId;
    }

    /**
     * @param string|null $navigationComponentId
     */
    public function setNavigationComponentId(?string $navigationComponentId): void
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
