<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits;

use PSB\PsbFoundation\Utility\ObjectUtility;
use ReflectionException;

/**
 * Trait ObjectToArrayTrait
 *
 * @package PSB\PsbFoundation\Traits
 */
trait ObjectToArrayTrait
{
    /**
     * @throws ReflectionException
     */
    public function toArray(): array
    {
        return ObjectUtility::toArray($this);
    }
}
