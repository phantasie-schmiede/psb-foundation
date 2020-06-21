<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use PSB\PsbFoundation\Domain\Model\AbstractFrontendUserRelatedModel;
use PSB\PsbFoundation\Traits\InjectionTrait;
use PSB\PsbFoundation\Utility\FrontendUserUtility;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
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
    use InjectionTrait;

    /**
     * @param object $object
     *
     * @throws AspectNotFoundException
     * @throws AspectPropertyNotFoundException
     * @throws Exception
     * @throws IllegalObjectTypeException
     */
    public function add($object): void
    {
        if ($object instanceof AbstractFrontendUserRelatedModel
            && null === $object->getFrontendUser()
        ) {
            $object->setFrontendUser(FrontendUserUtility::getCurrentUser());
        }

        parent::add($object);
    }

    /**
     * @param int $frontendUserId
     *
     * @return QueryResultInterface
     */
    public function findByFrontendUser(int $frontendUserId): QueryResultInterface
    {
        /** @var Query $query */
        $query = $this->createQuery();
        $query->matching($query->equals('frontendUser', $frontendUserId));

        return $query->execute();
    }

    /**
     * @return array|QueryResultInterface
     * @throws AspectNotFoundException
     * @throws AspectPropertyNotFoundException
     * @throws Exception
     */
    public function findTcaSelectItems()
    {
        return $this->findByFrontendUser(FrontendUserUtility::getCurrentUserId());
    }
}
