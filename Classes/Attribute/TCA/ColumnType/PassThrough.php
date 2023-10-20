<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Attribute\TCA\ColumnType;

use Attribute;

/**
 * Class PassThrough
 *
 * @package PSB\PsbFoundation\Attribute\TCA\ColumnType
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class PassThrough extends AbstractColumnType
{
    /**
     * Database definition has to be provided by extension author! Either in ext_tables.sql or the property
     * "databaseDefinition" of the attribute PSB\PsbFoundation\Attribute\TCA\Column.
     */
    public function getDatabaseDefinition(): string
    {
        return '';
    }
}
