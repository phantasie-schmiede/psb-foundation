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
 * Class PluginConfig
 *
 * Use this attribute for a plugin controller class.
 *
 * @package PSB\PsbFoundation\Attribute
 */
#[Attribute(Attribute::TARGET_CLASS)]
class PluginConfig extends AbstractAttribute
{
    /**
     * @param string $flexForm
     * @param string $group
     * @param string $iconIdentifier
     * @param string $title
     */
    public function __construct(
        protected string $flexForm = '',
        protected string $group = '',
        protected string $iconIdentifier = '',
        protected string $title = '',
    ) {
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
    public function getTitle(): string
    {
        return $this->title;
    }
}
