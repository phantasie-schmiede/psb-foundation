<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Attribute;

use PSB\PsbFoundation\Utility\ObjectUtility;
use ReflectionException;

/**
 * Class AbstractAttribute
 *
 * @package PSB\PsbFoundation\Attribute
 */
abstract class AbstractAttribute
{
    /**
     * @return array
     * @throws ReflectionException
     */
    public function toArray(): array
    {
        return ObjectUtility::toArray($this);
    }
}
