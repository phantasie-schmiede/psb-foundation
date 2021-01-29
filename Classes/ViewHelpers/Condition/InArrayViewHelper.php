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

namespace PSB\PsbFoundation\ViewHelpers\Condition;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class InArrayViewHelper
 *
 * @package PSB\PsbFoundation\ViewHelpers
 */
class InArrayViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('haystack', 'array', 'the data array', true);
        $this->registerArgument('needle', 'mixed', 'the value to search for', true);
        $this->registerArgument('strict', 'bool', 'apply strict comparison', false, true);
    }

    /**
     * @return string
     */
    public function render(): string
    {
        if (in_array($this->arguments['needle'], $this->arguments['haystack'], $this->arguments['strict'])) {
            return $this->renderChildren();
        }

        return '';
    }
}
