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
    public const DATABASE_DEFINITIONS = [
        'BITMAP_32'        => 'tinyint unsigned DEFAULT \'0\'',
        'DECIMAL'          => 'double(11,2) DEFAULT \'0.00\'',
        'INTEGER_SIGNED'   => 'int(11) DEFAULT \'0\'',
        'INTEGER_UNSIGNED' => 'int(11) unsigned DEFAULT \'0\'',
        'STRING'           => 'varchar(255) DEFAULT \'\'',
        'TEXT'             => 'text',
    ];

    /**
     * @return string
     */
    public function getDatabaseDefinition(): string
    {
        return defined('static::DATABASE_DEFINITION') ? static::DATABASE_DEFINITION : '';
    }

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
}
