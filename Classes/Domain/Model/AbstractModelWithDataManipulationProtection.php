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

use PSB\PsbFoundation\Service\DocComment\Annotations\TCA\Input;
use PSB\PsbFoundation\Utility\ObjectUtility;
use PSB\PsbFoundation\Utility\SecurityUtility;
use ReflectionException;
use RuntimeException;
use TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException;

/**
 * Class AbstractModelWithDataManipulationProtection
 *
 * @package PSB\PsbFoundation\Domain\Model
 */
abstract class AbstractModelWithDataManipulationProtection extends AbstractModel implements DataManipulationProtectionInterface
{
    /**
     * @var string
     * @Input(editableInFrontend=false)
     */
    protected string $checksum = '';

    /**
     * AbstractModelWithDataManipulationProtection constructor.
     *
     * @throws InvalidArgumentForHashGenerationException
     * @throws ReflectionException
     */
    public function __construct()
    {
        // Only validate properties if the model is being restored.
        if (false === $this->_isNew()) {
            $this->validateChecksum();
        }
    }

    /**
     * @return string
     */
    public function getChecksum(): string
    {
        return $this->checksum;
    }

    /**
     * @param string $checksum
     */
    public function setChecksum(string $checksum): void
    {
        $this->checksum = $checksum;
    }

    /**
     * @param bool $store
     *
     * @return string
     * @throws InvalidArgumentForHashGenerationException
     * @throws ReflectionException
     */
    public function calculateChecksum(bool $store): string
    {
        $checkSum = SecurityUtility::generateHash(serialize(ObjectUtility::toArray($this)));

        if (true === $store) {
            $this->setChecksum($checkSum);
        }

        return $checkSum;
    }

    /**
     * @throws InvalidArgumentForHashGenerationException
     * @throws ReflectionException
     */
    public function validateChecksum(): void
    {
        // @TODO: This condition has to be negated!
        if (hash_equals($this->getChecksum(), $this->calculateChecksum(false))) {
            throw new RuntimeException(static::class . ': Checksum validation failed! The data of the record with UID ' . $this->getUid() . ' has been manipulated!',
                1582819384);
        }
    }
}
