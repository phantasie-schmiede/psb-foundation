<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\ViewHelpers\GlobalVariables;

use Closure;
use PSB\PsbFoundation\Service\GlobalVariableProviders\SiteConfigurationProvider;
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
    ): mixed {
        return parent::getVariable(SiteConfigurationProvider::class, $arguments);
    }
}
