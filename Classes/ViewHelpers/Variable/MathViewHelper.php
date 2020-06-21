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

use InvalidArgumentException;
use JsonException;
use PSB\PsbFoundation\Traits\InjectionTrait;
use PSB\PsbFoundation\Utility\StringUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class MathViewHelper
 *
 * @package PSB\PsbFoundation\ViewHelpers
 */
class MathViewHelper extends AbstractViewHelper
{
    use InjectionTrait;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('a', 'mixed', 'Variable name for cycle (starts with 1)', true);
        $this->registerArgument('b', 'mixed', 'Variable name for index (starts with 0)', true);
        $this->registerArgument('operator', 'string', 'Mathematical operation to perform (+, -, *, /, **)', true);
    }

    /**
     * @return mixed
     * @throws Exception
     * @throws JsonException
     */
    public function render()
    {
        $a = $this->arguments['a'];
        $b = $this->arguments['b'];

        if (is_string($a)) {
            $a = StringUtility::convertString($a);
        }

        if (is_string($b)) {
            $b = StringUtility::convertString($b);
        }

        if (!is_numeric($a) && !is_numeric($b)) {
            throw $this->get(InvalidArgumentException::class, __CLASS__ . ': At least one argument is not numeric!',
                1558773027);
        }

        switch ($this->arguments['operator']) {
            case '+':
                return $a + $b;
            case '-':
                return $a - $b;
            case '*':
                return $a * $b;
            case '/':
                return $a / $b;
            case '**':
                return $a ** $b;
            default:
                throw $this->get(InvalidArgumentException::class, __CLASS__ . ': Operator not allowed!',
                    1558773161);
        }
    }
}
