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

namespace PSB\PsbFoundation\ViewHelpers;

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
