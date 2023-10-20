<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\ViewHelpers;

use Closure;
use PSB\PsbFoundation\Service\GlobalVariableService;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GlobalVariablesViewHelper
 *
 * @package PSB\PsbFoundation\ViewHelpers
 */
class GlobalVariablesViewHelper extends AbstractViewHelper
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
        return GlobalVariableService::get($arguments['path']);
    }

    /**
     * @return void
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('path', 'string', 'path segments must be separated by dots', true);
    }

    /**
     * @return void
     */
    public function render(): void
    {
        static::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
    }
}
