<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class FrontendVariableRepository
 *
 * @package PSB\PsbFoundation\Domain\Repository
 */
class FrontendVariableRepository extends Repository
{
    public function findByPid(int $pid): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()
            ->setRespectStoragePage(false);
        $query->matching($query->equals('pid', $pid));
        $query->setOrderings([
            'pid' => QueryInterface::ORDER_ASCENDING,
        ]);

        return $query->execute();
    }
}
