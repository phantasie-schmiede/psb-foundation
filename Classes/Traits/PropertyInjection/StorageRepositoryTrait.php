<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits\PropertyInjection;

use TYPO3\CMS\Core\Resource\StorageRepository;

/**
 * Trait StorageRepositoryTrait
 *
 * @package PSB\PsbFoundation\Traits\PropertyInjection
 */
trait StorageRepositoryTrait
{
    /**
     * @var StorageRepository|null
     */
    protected ?StorageRepository $storageRepository = null;

    /**
     * @param StorageRepository $storageRepository
     *
     * @return void
     */
    public function injectStorageRepository(StorageRepository $storageRepository): void
    {
        $this->storageRepository = $storageRepository;
    }
}
