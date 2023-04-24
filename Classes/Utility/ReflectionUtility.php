<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Utility;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use ReflectionProperty;
use function count;

/**
 * Class ReflectionUtility
 *
 * @package PSB\PsbFoundation\Utility
 */
class ReflectionUtility
{
    /**
     * @param string                                                                                                    $attributeClass
     * @param ReflectionClass|ReflectionClassConstant|ReflectionFunctionAbstract|ReflectionParameter|ReflectionProperty $reflection
     *
     * @return object|null
     */
    public static function getAttributeInstance(
        string                                                                                                    $attributeClass,
        ReflectionClass|ReflectionClassConstant|ReflectionFunctionAbstract|ReflectionParameter|ReflectionProperty $reflection,
    ): ?object {
        $attributes = $reflection->getAttributes($attributeClass, ReflectionAttribute::IS_INSTANCEOF);

        return 0 < count($attributes) ? $attributes[0]->newInstance() : null;
    }
}
