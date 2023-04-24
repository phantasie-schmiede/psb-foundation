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
 * Enum DateType
 *
 * @package PSB\PsbFoundation\Enum
 */
enum DateType: string
{
    case date     = 'date';
    case datetime = 'datetime';
    case time     = 'time';
}
