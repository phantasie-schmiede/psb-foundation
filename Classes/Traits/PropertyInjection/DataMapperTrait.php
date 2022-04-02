<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits\PropertyInjection;

use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

/**
 * Trait DataMapperTrait
 *
 * @package PSB\PsbFoundation\Traits\PropertyInjection
 */
trait DataMapperTrait
{
    /**
     * @var DataMapper
     */
    protected DataMapper $dataMapper;

    /**
     * @param DataMapper $dataMapper
     *
     * @return void
     */
    public function injectDataMapper(DataMapper $dataMapper): void
    {
        $this->dataMapper = $dataMapper;
    }
}
