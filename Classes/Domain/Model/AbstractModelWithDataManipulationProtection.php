<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Domain\Model;

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

use PSB\PsbFoundation\Utility\SecurityUtility;
use ReflectionObject;
use RuntimeException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class AbstractModelWithDataManipulationProtection
 * @package PSB\PsbFoundation\Domain\Model
 */
abstract class AbstractModelWithDataManipulationProtection extends AbstractEntity implements DataManipulationProtectionInterface
{
    /**
     * @var string
     */
    protected string $checksum;

    /**
     * AbstractModelWithDataManipulationProtection constructor.
     */
    public function __construct()
    {
        // Only validate properties if the model is being restored.
        if (false === $this->_isNew()) {
            $this->validateChecksum();
        }
    }

    /**
     * @param bool $store
     *
     * @return string
     */
    public function calculateChecksum(bool $store): string
    {
        $reflectionObject = GeneralUtility::makeInstance(ReflectionObject::class, $this);
        $properties = ReflectionObject::export($reflectionObject, true);
        $checkSum = hash_hmac('sha256', $properties, SecurityUtility::getEncryptionKey());

        if (true === $store) {
            $this->checksum = $checkSum;
        }

        return $checkSum;
    }

    public function validateChecksum(): void
    {
        if (hash_equals($this->checksum, $this->calculateChecksum(false))) {
            throw new RuntimeException(__CLASS__ . ': Checksum validation failed! The data of the record with UID ' . $this->getUid() . ' has been manipulated!',
                1582819384);
        }
    }
}
