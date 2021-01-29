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
 * Class GetClassNameViewHelper
 *
 * @package PSB\PsbFoundation\ViewHelpers
 */
class GetClassNameViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('object', 'object', 'Object whose name should be retrieved', true);
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $className = get_class($this->arguments['object']);
        $className = explode('\\', $className);

        return array_pop($className);
    }
}
