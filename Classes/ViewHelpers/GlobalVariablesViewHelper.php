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
use Exception;
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
     * @throws Exception
     */
    public static function renderStatic(
        array                     $arguments,
        Closure                   $renderChildrenClosure,
        RenderingContextInterface $renderingContext,
    ): mixed {
        return GlobalVariableService::get($arguments['path'], $arguments['strict'], $arguments['fallback']);
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument(
            'fallback',
            'mixed',
            'fallback value when path is invalid and strict is set to false',
        );
        $this->registerArgument('path', 'string', 'path segments must be separated by dots', true);
        $this->registerArgument(
            'strict',
            'bool',
            'invalid path throws an exception on true or returns a fallback value on false',
            false,
            true
        );
    }
}
