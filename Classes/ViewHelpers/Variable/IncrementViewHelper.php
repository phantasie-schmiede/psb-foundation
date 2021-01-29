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

namespace PSB\PsbFoundation\ViewHelpers\Variable;

use InvalidArgumentException;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * Class IncrementViewHelper
 *
 * @package PSB\PsbFoundation\ViewHelpers\Variable
 */
class IncrementViewHelper extends AbstractViewHelper
{
    /**
     * @throws Exception
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('step', 'integer', 'amount added to the variable\'s value', false, 1);
        $this->registerArgument('variable', 'string', 'variable to be incremented', true);
    }

    public function render(): void
    {
        $templateVariableContainer = $this->renderingContext->getVariableProvider();

        if (!$templateVariableContainer->exists($this->arguments['variable'])) {
            throw new InvalidArgumentException(
                __CLASS__ . ': Variable "' . $this->arguments['variable'] . '" has not been defined in this context!',
                1549454307
            );
        }

        if (!is_numeric($this->arguments['step'])) {
            throw new InvalidArgumentException(
                __CLASS__ . ': Argument "step" is not numeric (value: "' . $this->arguments['step'] . '")!',
                1549455466
            );
        }

        $value = $templateVariableContainer->get($this->arguments['variable']);

        if (!is_numeric($value)) {
            throw new InvalidArgumentException(
                __CLASS__ . ': Variable "' . $this->arguments['variable'] . '" is not numeric!',
                1549454962
            );
        }

        $templateVariableContainer->add($this->arguments['variable'],
            $value + $this->arguments['step']);
    }
}
