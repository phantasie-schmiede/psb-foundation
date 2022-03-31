<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Annotation\TCA\Column;

/**
 * Class PassThrough
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA\Column
 */
class PassThrough extends AbstractColumnAnnotation
{
    public const TYPE = self::TYPES['PASSTHROUGH'];
}
