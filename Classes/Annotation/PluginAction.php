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
 * Class PluginAction
 *
 * Use this annotation for methods in a plugin controller.
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation
 */
class PluginAction extends AbstractAnnotation
{
    /**
     * Marks the default action of the controller (executed, when no specific action is given in a request).
     * @var bool
     */
    protected bool $default = false;

    /**
     * Add this action to the list of uncached actions
     *
     * @var bool
     */
    protected bool $uncached = false;

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->default;
    }

    /**
     * @param bool $default
     */
    public function setDefault(bool $default): void
    {
        $this->default = $default;
    }

    /**
     * @return bool
     */
    public function isUncached(): bool
    {
        return $this->uncached;
    }

    /**
     * @param bool $uncached
     */
    public function setUncached(bool $uncached): void
    {
        $this->uncached = $uncached;
    }
}
