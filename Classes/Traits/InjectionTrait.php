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

namespace PSB\PsbFoundation\Traits;

use PSB\PsbFoundation\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Trait InjectionTrait
 * @package PSB\PsbFoundation\Traits
 */
trait InjectionTrait
{
    /**
     * @param string $className
     * @param mixed  ...$arguments
     *
     * @return object
     * @throws Exception
     */
    protected function get(string $className, ...$arguments): object
    {
        return ObjectUtility::get($className, ...$arguments);
    }
}
