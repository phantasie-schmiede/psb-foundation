<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Enum;

/**
 * Enum Relationship
 *
 * @package PSB\PsbFoundation\Enum
 */
enum Relationship: string
{
    case manyToMany = 'manyToMany';
    case oneToMany  = 'oneToMany';
    case oneToOne   = 'oneToOne';
}
