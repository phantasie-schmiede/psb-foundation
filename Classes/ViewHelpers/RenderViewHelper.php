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

namespace PSB\PsbFoundation\ViewHelpers;

use Closure;
use PSB\PsbFoundation\Service\GlobalVariableService;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class RenderViewHelper
 *
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
