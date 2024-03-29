<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Exceptions;

use Exception;

/**
 * Class BaseException
 *
 * @package PSB\PsbFoundation\Exceptions
 */
class BaseException extends Exception
{
    public function __toString(): string
    {
        return __CLASS__ . ': [' . $this->code . ']: ' . $this->message . LF;
    }
}
