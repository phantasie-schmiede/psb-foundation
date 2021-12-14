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

use PSB\PsbFoundation\Service\GlobalVariableService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class AbstractGlobalVariablesViewHelper
 *
 * @package PSB\PsbFoundation\ViewHelpers\GlobalVariables
 */
abstract class AbstractGlobalVariablesViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('path', 'string', 'array path separated with dots');
    }

    /**
     * @return mixed
     */
    public function render()
    {
        return static::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
    }

    /**
     * @param string $baseKey
     * @param array  $arguments
     *
     * @return mixed
     */
    protected static function getVariable(string $baseKey, array $arguments)
    {
        if (!empty($arguments['path'])) {
            $baseKey .= '.' . $arguments['path'];
        }

        return GlobalVariableService::get($baseKey);
    }
}
