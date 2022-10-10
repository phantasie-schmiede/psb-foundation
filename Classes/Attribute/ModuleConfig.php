<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Attribute;

use Attribute;

/**
 * Class ModuleConfig
 *
 * Use this attribute for a module controller class.
 *
 * @package PSB\PsbFoundation\Attribute
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ModuleConfig extends AbstractAttribute
{
    /**
     * @param string      $access
     * @param string|null $iconIdentifier
     * @param string|null $labels
     * @param string      $mainModuleName
     * @param string|null $navigationComponentId
     * @param string      $position
     */
    public function __construct(
        protected string $access = 'group, user',
        protected ?string $iconIdentifier = null,
        protected ?string $labels = null,
        protected string $mainModuleName = 'web',
        protected ?string $navigationComponentId = null,
        protected string $position = '',
    ) {
    }

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
}
