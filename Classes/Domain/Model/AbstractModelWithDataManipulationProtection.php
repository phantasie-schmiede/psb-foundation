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

namespace PSB\PsbFoundation\Domain\Model;

use PSB\PsbFoundation\Annotation\TCA\Input;
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
