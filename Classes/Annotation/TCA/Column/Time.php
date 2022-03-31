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
 * Class Time
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA\Column
 */
class Time extends DateTime
{
    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/DateTime/Properties/DbType.html
     */
    protected ?string $dbType = 'time';

    /**
     * @var string
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Input/Properties/Eval.html
     */
    protected string $eval = 'null, time';
}
