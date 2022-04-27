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
     * @return string|null
     */
    public function getIconIdentifier(): ?string
    {
        return $this->iconIdentifier;
    }

    /**
     * @return string|null
     */
    public function getLabels(): ?string
    {
        return $this->labels;
    }

    /**
     * @return string
     */
    public function getMainModuleName(): string
    {
        return $this->mainModuleName;
    }

    /**
     * @return string|null
     */
    public function getNavigationComponentId(): ?string
    {
        return $this->navigationComponentId;
    }

    /**
     * @return string
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * @param string $access
     *
     * @return void
     */
    public function setAccess(string $access): void
    {
        $this->access = $access;
    }

    /**
     * @param string|null $iconIdentifier
     *
     * @return void
     */
    public function setIconIdentifier(?string $iconIdentifier): void
    {
        $this->iconIdentifier = $iconIdentifier;
    }

    /**
     * @param string|null $labels
     *
     * @return void
     */
    public function setLabels(?string $labels): void
    {
        $this->labels = $labels;
    }

    /**
     * @param string $mainModuleName
     *
     * @return void
     */
    public function setMainModuleName(string $mainModuleName): void
    {
        $this->mainModuleName = $mainModuleName;
    }

    /**
     * @param string|null $navigationComponentId
     *
     * @return void
     */
    public function setNavigationComponentId(?string $navigationComponentId): void
    {
        $this->navigationComponentId = $navigationComponentId;
    }

    /**
     * @param string $position
     *
     * @return void
     */
    public function setPosition(string $position): void
    {
        $this->position = $position;
    }
}
