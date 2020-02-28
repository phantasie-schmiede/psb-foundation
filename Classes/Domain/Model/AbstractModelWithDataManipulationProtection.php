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

use Error;
use PSB\PsbFoundation\Service\DocComment\Annotations\TcaFieldConfig;
use PSB\PsbFoundation\Utility\SecurityUtility;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException;

/**
 * Class AbstractModelWithDataManipulationProtection
 * @package PSB\PsbFoundation\Domain\Model
 */
abstract class AbstractModelWithDataManipulationProtection extends AbstractEntity implements DataManipulationProtectionInterface
{
    /**
     * @var string
     * @TcaFieldConfig(type="string")
     */
    protected string $checksum;

    /**
     * AbstractModelWithDataManipulationProtection constructor.
     * @throws InvalidArgumentForHashGenerationException
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
     */
    public function calculateChecksum(bool $store): string
    {
        $checkSum = SecurityUtility::generateHash(serialize($this->toArray()));

        if (true === $store) {
            $this->setChecksum($checkSum);
        }

        return $checkSum;
    }

    /**
     * @TODO: move this into a trait as it is used elsewhere, too
     * @return array
     */
    public function toArray(): array
    {
        $arrayRepresentation = [];
        $reflectionClass = GeneralUtility::makeInstance(ReflectionClass::class, static::class);
        $properties = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            try {
                $property->setAccessible(true);
                $getterMethodName = 'get' . ucfirst($property->getName());

                if ($reflectionClass->hasMethod($getterMethodName)) {
                    $reflectionMethod = GeneralUtility::makeInstance(ReflectionMethod::class, $this, $getterMethodName);
                    $value = $reflectionMethod->invoke($this);

                    if (!empty($value)) {
                        $arrayRepresentation[$property->getName()] = $value;
                    }
                }
            } catch (Error $error) {
                // Property is not initialized yet.
                continue;
            }
        }

        return $arrayRepresentation;
    }

    /**
     * @throws InvalidArgumentForHashGenerationException
     */
    public function validateChecksum(): void
    {
        if (hash_equals($this->getChecksum(), $this->calculateChecksum(false))) {
            throw new RuntimeException(__CLASS__ . ': Checksum validation failed! The data of the record with UID ' . $this->getUid() . ' has been manipulated!',
                1582819384);
        }
    }
}
