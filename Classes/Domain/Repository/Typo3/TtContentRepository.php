<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace PSB\PsbFoundation\Domain\Repository\Typo3;

use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class TtContentRepository
 *
 * @package PSB\PsbFoundation\Domain\Repository\Typo3
 */
class TtContentRepository extends Repository
{
    /**
     * @param string $cType
     *
     * @return QueryResultInterface
     */
    public function findByCType(string $cType): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->equals('cType', $cType));

        return $query->execute();
    }
}
