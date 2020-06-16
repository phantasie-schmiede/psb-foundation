<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Service\DocComment\Annotations;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019-2020 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

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
