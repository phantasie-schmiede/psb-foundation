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

namespace PSB\PsbFoundation\Service\DocComment\Annotations;

/**
 * Class ModuleAction
 *
 * Use this annotation for methods in a module controller.
 *
 * @Annotation
 * @package PSB\PsbFoundation\Service\DocComment\Annotations
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
     * @var bool
     */
    protected bool $doNotRender = false;

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
    public function isDoNotRender(): bool
    {
        return $this->doNotRender;
    }

    /**
     * @param bool $doNotRender
     */
    public function setDoNotRender(bool $doNotRender): void
    {
        $this->doNotRender = $doNotRender;
    }
}
