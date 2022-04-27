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
 * Class PluginConfig
 *
 * Use this annotation for a plugin controller class.
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation
 */
class PluginConfig extends AbstractAnnotation
{
    /**
     * @var string
     */
    protected string $flexForm = '';

    /**
     * @var string
     */
    protected string $group = '';

    /**
     * @var string
     */
    protected string $iconIdentifier = '';

    /**
     * @var string
     */
    protected string $title = '';

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

    /**
     * @param string $flexForm
     *
     * @return void
     */
    public function setFlexForm(string $flexForm): void
    {
        $this->flexForm = $flexForm;
    }

    /**
     * @param string $group
     *
     * @return void
     */
    public function setGroup(string $group): void
    {
        $this->group = $group;
    }

    /**
     * @param string $iconIdentifier
     *
     * @return void
     */
    public function setIconIdentifier(string $iconIdentifier): void
    {
        $this->iconIdentifier = $iconIdentifier;
    }

    /**
     * @param string $title
     *
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
