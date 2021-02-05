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

use PSB\PsbFoundation\Domain\Model\DataManipulationProtectionInterface;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

/**
 * Class AbstractRepository
 *
 * @package PSB\PsbFoundation\Domain\Repository
 */
abstract class AbstractModelWithDataManipulationProtectionRepository extends AbstractRepository
{
    /**
     * @param object $object
     *
     * @throws IllegalObjectTypeException
     */
    public function add($object): void
    {
        if ($object instanceof DataManipulationProtectionInterface) {
            $object->calculateCheckSum(true);
        }

        parent::add($object);
    }

    /**
     * @param object $modifiedObject
     *
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function update($modifiedObject): void
    {
        if ($modifiedObject instanceof DataManipulationProtectionInterface) {
            $modifiedObject->calculateCheckSum(true);
        }

        parent::update($modifiedObject);
    }
}
