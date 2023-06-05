<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Attribute\TCA\ColumnType;

use PSB\PsbFoundation\Service\LocalizationService;

/**
 * Interface ColumnTypeWithItemsInterface
 *
 * @package PSB\PsbFoundation\Attribute\TCA\ColumnType
 */
interface ColumnTypeWithItemsInterface extends ColumnTypeInterface
{
    /**
     * @param LocalizationService $localizationService
     * @param string              $labelPath
     *
     * @return void
     */
    public function processItems(LocalizationService $localizationService, string $labelPath = ''): void;
}
