<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Attribute\TCA\ColumnType;

use PSB\PsbFoundation\Attribute\AbstractAttribute;
use ReflectionException;

/**
 * Class AbstractColumnType
 *
 * @package PSB\PsbFoundation\Attribute\TCA\ColumnType
 */
abstract class AbstractColumnType extends AbstractAttribute implements ColumnTypeInterface
{
    /**
     * Returns the short class name (lower case) for ['config']['type'].
     *
     * @return string
     */
    public function getType(): string
    {
        $classNameParts = explode('\\', static::class);

        return strtolower(array_pop($classNameParts));
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function toArray(): array
    {
        $configuration = parent::toArray();
        $configuration['type'] = $this->getType();

        return $configuration;
    }

    /**
     * @return string
     */
    public function getDatabaseDefinition(): string
    {
        return defined('static::DATABASE_DEFINITION') ? static::DATABASE_DEFINITION : '';
    }
}
