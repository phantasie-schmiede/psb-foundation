<?php
declare(strict_types=1);

namespace PSB\PsbFoundation\ViewHelpers;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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

use Closure;
use PSB\PsbFoundation\Services\GlobalVariableService;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class RenderViewHelper
 * @package PSB\PsbFoundation\ViewHelpers
 */
class RenderViewHelper extends \TYPO3Fluid\Fluid\ViewHelpers\RenderViewHelper
{
    /**
     * @param array                     $arguments
     * @param Closure                   $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return mixed
     */
    public static function renderStatic(
        array $arguments,
        Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $globalVariables = GlobalVariableService::getGlobalVariables();

        foreach ($globalVariables as $key => $value) {
            if (!isset($arguments['arguments'][$key])) {
                $arguments['arguments'][$key] = $value;
            }
        }

        return parent::renderStatic($arguments, $renderChildrenClosure, $renderingContext);
    }
}
