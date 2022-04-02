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
 * Class ModuleAction
 *
 * Use this annotation for methods in a module controller.
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation
 */
class ModuleAction extends AbstractAnnotation
{
    /**
     * Marks the default action of the controller (executed, when no specific action is given in a request).
     *
     * @var bool
     */
    protected bool $default = false;

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->default;
    }

    /**
     * @param bool $default
     *
     * @return void
     */
    public function setDefault(bool $default): void
    {
        $this->default = $default;
    }
}
