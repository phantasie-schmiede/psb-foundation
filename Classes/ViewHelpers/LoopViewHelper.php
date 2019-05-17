<?php
declare(strict_types=1);

namespace PSB\PsbFoundation\ViewHelpers;

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

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class LoopViewHelper
 * @package PSB\PsbFoundation\ViewHelpers
 */
class LoopViewHelper extends AbstractViewHelper
{
    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @var array
     */
    private $variableBackups;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('cycle', 'string', 'Variable name for cycle (starts with 1)', false, 'cycle');
        $this->registerArgument('index', 'string', 'Variable name for index (starts with 0)', false, 'index');
        $this->registerArgument('iterations', 'int', 'Number of loops');
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $content = '';
        $renderChildrenClosure = $this->buildRenderChildrenClosure();
        $templateVariableContainer = $this->renderingContext->getVariableProvider();

        $this->backupVariables([$this->arguments['cycle'], $this->arguments['index']]);

        for ($i = 0; $i < $this->arguments['iterations']; $i++) {
            $templateVariableContainer->add($this->arguments['cycle'], $i + 1);
            $templateVariableContainer->add($this->arguments['index'], $i);
            $content .= $renderChildrenClosure();
            $templateVariableContainer->remove($this->arguments['cycle']);
            $templateVariableContainer->remove($this->arguments['index']);
        }

        $this->restoreVariables([$this->arguments['cycle'], $this->arguments['index']]);

        return $content;
    }

    /**
     * @param array $variables
     */
    private function backupVariables(array $variables): void
    {
        $templateVariableContainer = $this->renderingContext->getVariableProvider();

        foreach ($variables as $variable) {
            if ($templateVariableContainer->exists($variable)) {
                $this->variableBackups[$variable] = $templateVariableContainer->get($variable);
            }
        }
    }

    /**
     * @param array $variables
     */
    private function restoreVariables(array $variables): void
    {
        $templateVariableContainer = $this->renderingContext->getVariableProvider();

        foreach ($variables as $variable) {
            if (isset($this->variableBackups[$variable])) {
                $templateVariableContainer->add($variable, $this->variableBackups[$variable]);
            }
        }
    }
}
