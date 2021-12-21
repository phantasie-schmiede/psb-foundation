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

namespace PSB\PsbFoundation\Annotation\TCA;

use PSB\PsbFoundation\Library\TcaFields;

/**
 * Class Mm
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA
 */
class Mm extends Select
{
    public const TYPE = TcaFields::TYPES['MM'];

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
     * name of the mm-table
     *
     * @var string
     */
    protected string $mm = '';

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
     */
    public function getMmOppositeField(): ?string
    {
        if (null === $this->mmOppositeField || null === $this->tcaService) {
            return null;
        }

        return $this->tcaService->convertPropertyNameToColumnName($this->mmOppositeField);
    }

    /**
     * @param string|null $mmOppositeField
     */
    public function setMmOppositeField(?string $mmOppositeField): void
    {
        $this->mmOppositeField = $mmOppositeField;
    }
}
