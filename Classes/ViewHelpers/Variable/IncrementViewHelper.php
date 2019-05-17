<?php
declare(strict_types=1);

namespace PSB\PsbFoundation\ViewHelpers\Variable;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 PSG Web Team <webdev@plan.de>, PSG Plan Service Gesellschaft mbH
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

use InvalidArgumentException;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * Class IncrementViewHelper
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
                __CLASS__.': Variable "'.$this->arguments['variable'].'" has not been defined in this context!',
                1549454307
            );
        }

        if (!is_numeric($this->arguments['step'])) {
            throw new InvalidArgumentException(
                __CLASS__.': Argument "step" is not numeric (value: "'.$this->arguments['step'].'")!',
                1549455466
            );
        }

        $value = $templateVariableContainer->get($this->arguments['variable']);

        if (!is_numeric($value)) {
            throw new InvalidArgumentException(
                __CLASS__.': Variable "'.$this->arguments['variable'].'" is not numeric!',
                1549454962
            );
        }

        $templateVariableContainer->add($this->arguments['variable'],
            $value + $this->arguments['step']);
    }
}
