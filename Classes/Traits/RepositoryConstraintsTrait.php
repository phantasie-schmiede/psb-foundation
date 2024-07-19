<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits;

use TYPO3\CMS\Extbase\Persistence\Generic\Qom\OrInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use function count;

/**
 * Trait RepositoryConstraintsTrait
 *
 * @package PSB\PsbFoundation\Traits
 */
trait RepositoryConstraintsTrait
{
    protected function applyConstraints(array $constraints, QueryInterface $query): void
    {
        switch (count($constraints)) {
            case 0:
                break;
            case 1:
                $query->matching($constraints[0]);
                break;
            default:
                $query->matching($query->logicalAnd(...$constraints));
        }
    }
}
