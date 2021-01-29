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
