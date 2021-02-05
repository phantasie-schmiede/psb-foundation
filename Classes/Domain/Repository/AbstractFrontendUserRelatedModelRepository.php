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

namespace PSB\PsbFoundation\Domain\Repository;

use PSB\PsbFoundation\Domain\Model\AbstractFrontendUserRelatedModel;
use PSB\PsbFoundation\Traits\Properties\FrontendUserServiceTrait;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class AbstractFrontendUserRelatedModelRepository
 *
 * This repository lets you find records that are connected to a certain frontend user. Goes hand in hand with a model
 * extending \PSB\PsbFoundation\Domain\Model\AbstractFrontendUserRelatedModel
 *
 * @package PSB\PsbFoundation\Domain\Repository
 */
abstract class AbstractFrontendUserRelatedModelRepository extends AbstractModelWithDataManipulationProtectionRepository
{
    use FrontendUserServiceTrait;

    /**
     * @param object $object
     *
     * @throws AspectNotFoundException
     * @throws AspectPropertyNotFoundException
     * @throws IllegalObjectTypeException
     */
    public function add($object): void
    {
        if ($object instanceof AbstractFrontendUserRelatedModel
            && null === $object->getFrontendUser()
        ) {
            $object->setFrontendUser($this->frontendUserService->getCurrentUser());
        }

        parent::add($object);
    }

    /**
     * @param int|null $frontendUserId
     *
     * @return QueryResultInterface
     * @throws AspectNotFoundException
     * @throws AspectPropertyNotFoundException
     */
    public function findByFrontendUser(int $frontendUserId = null): QueryResultInterface
    {
        if (null === $frontendUserId) {
            $frontendUserId = $this->frontendUserService->getCurrentUserId();
        }

        /** @var Query $query */
        $query = $this->createQuery();
        $query->matching($query->equals('frontendUser', $frontendUserId));

        return $query->execute();
    }

    /**
     * @return array|QueryResultInterface
     * @throws AspectNotFoundException
     * @throws AspectPropertyNotFoundException
     */
    public function findTcaSelectItems()
    {
        return $this->findByFrontendUser();
    }
}
