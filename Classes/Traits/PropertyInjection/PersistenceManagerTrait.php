<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits\PropertyInjection;

use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

/**
 * Trait PersistenceManagerTrait
 *
 * @package PSB\PsbFoundation\Traits\PropertyInjection
 */
trait PersistenceManagerTrait
{
    /**
     * @var PersistenceManagerInterface
     */
    protected PersistenceManagerInterface $persistenceManager;

    /**
     * @param PersistenceManagerInterface $persistenceManager
     *
     * @return void
     */
    public function injectPersistenceManager(PersistenceManagerInterface $persistenceManager): void
    {
        $this->persistenceManager = $persistenceManager;
    }
}
