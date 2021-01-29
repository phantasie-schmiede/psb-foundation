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
 * Class ArrayMergeViewHelper
 *
 * @package PSB\PsbFoundation\ViewHelpers\Variable
 */
class ArrayMergeViewHelper extends AbstractViewHelper
{
    /**
     * @throws Exception
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('arrays', 'array', 'arrays to be merged', true);
        $this->registerArgument('as', 'string', 'variable name of the merged result', true);
        $this->registerArgument('overwrite', 'boolean', 'overwrites the variable if already existing', false, false);
    }

    public function render(): void
    {
        $templateVariableContainer = $this->renderingContext->getVariableProvider();

        if (!$this->arguments['overwrite'] && $templateVariableContainer->exists($this->arguments['as'])) {
            throw new InvalidArgumentException(
                __CLASS__ . ': Variable "' . $this->arguments['as'] . '" already exists!',
                1549520834
            );
        }

        array_walk($this->arguments['arrays'], static function (&$value) {
            if (!is_array($value)) {
                $value = [];
            }
        });

        $templateVariableContainer->add($this->arguments['as'], array_merge(...$this->arguments['arrays']));
    }
}
