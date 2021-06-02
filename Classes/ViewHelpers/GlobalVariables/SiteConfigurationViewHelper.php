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

namespace PSB\PsbFoundation\ViewHelpers\GlobalVariables;

use Closure;
use PSB\PsbFoundation\Service\GlobalVariableProviders\SiteConfigurationProvider;
use PSB\PsbFoundation\Service\GlobalVariableService;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class SiteConfigurationViewHelper
 *
 * @package PSB\PsbFoundation\ViewHelpers\GlobalVariables
 */
class SiteConfigurationViewHelper extends AbstractGlobalVariablesViewHelper
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
        return GlobalVariableService::get(SiteConfigurationProvider::getKey() . '.' . $arguments['path']);
    }
}
