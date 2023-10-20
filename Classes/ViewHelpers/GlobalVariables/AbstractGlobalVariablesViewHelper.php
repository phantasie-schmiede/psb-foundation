<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\ViewHelpers\GlobalVariables;

use PSB\PsbFoundation\Service\GlobalVariableService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class AbstractGlobalVariablesViewHelper
 *
 * @package PSB\PsbFoundation\ViewHelpers\GlobalVariables
 */
abstract class AbstractGlobalVariablesViewHelper extends AbstractViewHelper
{
    /**
     * @param string $baseKey
     * @param array  $arguments
     *
     * @return mixed
     */
    protected static function getVariable(string $baseKey, array $arguments): mixed
    {
        if (!empty($arguments['path'])) {
            $baseKey .= '.' . $arguments['path'];
        }

        return GlobalVariableService::get($baseKey);
    }

    /**
     * @return void
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('path', 'string', 'array path separated with dots');
    }

    /**
     * @return mixed
     */
    public function render(): mixed
    {
        return static::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
    }
}
