<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\ViewHelpers;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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
