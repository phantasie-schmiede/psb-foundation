<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\ViewHelpers\GlobalVariables;

use Exception;
use PSB\PsbFoundation\Service\GlobalVariableService;
use PSB\PsbFoundation\ViewHelpers\GlobalVariablesViewHelper;

/**
 * Class AbstractGlobalVariablesViewHelper
 *
 * @package PSB\PsbFoundation\ViewHelpers\GlobalVariables
 */
abstract class AbstractGlobalVariablesViewHelper extends GlobalVariablesViewHelper
{
    /**
     * @param string $baseKey
     * @param array  $arguments
     *
     * @return mixed
     * @throws Exception
     */
    protected static function getVariable(string $baseKey, array $arguments): mixed
    {
        if (!empty($arguments['path'])) {
            $baseKey .= '.' . $arguments['path'];
        }

        return GlobalVariableService::get($baseKey, $arguments['strict'], $arguments['fallback']);
    }

    /**
     * @return void
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();

        // Overwrite this argument to make it optional as the extending ViewHelpers define a base path.
        $this->registerArgument('path', 'string', 'path segments must be separated by dots');
    }
}
