<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits\PropertyInjection;

use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Trait ConnectionPoolTrait
 *
 * @package PSB\PsbFoundation\Traits\PropertyInjection
 */
trait ConnectionPoolTrait
{
    /**
     * @var ConnectionPool
     */
    protected ConnectionPool $connectionPool;

    /**
     * @param ConnectionPool $connectionPool
     *
     * @return void
     */
    public function injectConnectionPool(ConnectionPool $connectionPool): void
    {
        $this->connectionPool = $connectionPool;
    }
}
