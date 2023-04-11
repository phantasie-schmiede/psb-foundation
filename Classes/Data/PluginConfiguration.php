<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Data;

/**
 * Class PluginConfiguration
 *
 * @package PSB\PsbFoundation\Data
 */
class PluginConfiguration
{
    /**
     * @param string $key
     * @param array $controllers
     * @param string $flexForm
     * @param string $group
     * @param string $iconIdentifier
     * @param string $title
     */
    public function __construct(
        protected string $key,
        protected array  $controllers = [],
        protected string $flexForm = '',
        protected string $group = '',
        protected string $iconIdentifier = '',
        protected string $title = '',
    )
    {
    }

    /**
     * @return array
     */
    public function getControllers(): array
    {
        return $this->controllers;
    }

    /**
     * @return string
     */
    public function getFlexForm(): string
    {
        return $this->flexForm;
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @return string
     */
    public function getIconIdentifier(): string
    {
        return $this->iconIdentifier;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }
}
