<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Service\DocComment\Annotations\TCA;

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

use PSB\PsbFoundation\Exceptions\AnnotationException;
use PSB\PsbFoundation\Service\Configuration\Fields;
use PSB\PsbFoundation\Utility\ExtensionInformationUtility;
use ReflectionException;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException;

/**
 * Class Mm
 *
 * @Annotation
 * @package PSB\PsbFoundation\Service\DocComment\Annotations\TCA
 */
class Mm extends Select
{
    public const TYPE = Fields::FIELD_TYPES['MM'];

    /**
     * @var int
     */
    protected int $autoSizeMax = 30;

    /**
     * 0 means no limit theoretically (max items allowed by core currently are 99999)
     *
     * @var int
     */
    protected int $maxItems = 0;

    /**
     * @var string
     */
    protected string $mm;

    /**
     * @var bool|null
     */
    protected ?bool $mmHasUidField = null;

    /**
     * @var string|null
     */
    protected ?string $mmOppositeField = null;

    /**
     * @var string
     */
    protected string $renderType = 'selectMultipleSideBySide';

    /**
     * @var int
     */
    protected int $size = 10;

    /**
     * @return string
     */
    public function getMm(): string
    {
        return $this->mm;
    }

    /**
     * @param string $mm
     */
    public function setMm(string $mm): void
    {
        $this->mm = $mm;
    }

    /**
     * @return bool|null
     */
    public function getMmHasUidField(): ?bool
    {
        return $this->mmHasUidField;
    }

    /**
     * @param bool|null $mmHasUidField
     */
    public function setMmHasUidField(?bool $mmHasUidField): void
    {
        $this->mmHasUidField = $mmHasUidField;
    }

    /**
     * @return string|null
     * @throws AnnotationException
     * @throws Exception
     * @throws InvalidArgumentForHashGenerationException
     * @throws ReflectionException
     */
    public function getMmOppositeField(): ?string
    {
        if (null === $this->mmOppositeField) {
            return null;
        }

        return ExtensionInformationUtility::convertPropertyNameToColumnName($this->mmOppositeField);
    }

    /**
     * @param string|null $mmOppositeField
     */
    public function setMmOppositeField(?string $mmOppositeField): void
    {
        $this->mmOppositeField = $mmOppositeField;
    }
}
