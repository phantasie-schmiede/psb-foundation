<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Attribute\TCA\ColumnType;

/**
 * Interface ColumnTypeWithItemsInterface
 *
 * @package PSB\PsbFoundation\Attribute\TCA\ColumnType
 */
interface ColumnTypeWithItemsInterface extends ColumnTypeInterface
{
    public function processItems(string $labelPath = ''): void;
}
