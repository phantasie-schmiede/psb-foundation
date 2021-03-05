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
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @param string $group
     */
    public function setGroup(string $group): void
    {
        $this->group = $group;
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
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
