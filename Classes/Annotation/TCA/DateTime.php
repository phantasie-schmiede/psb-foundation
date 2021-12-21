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
 * Class DateTime
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA
 */
class DateTime extends Input
{
    public const TYPE = TcaFields::TYPES['DATETIME'];

    /**
     * @var string|null
     */
    protected ?string $dbType = 'datetime';

    /**
     * @var string
     */
    protected string $eval = 'datetime, null';

    /**
     * @var string|null
     */
    protected ?string $renderType = 'inputDateTime';

    /**
     * @var int
     */
    protected int $size = 12;

    /**
     * @return string|null
     */
    public function getDbType(): ?string
    {
        return $this->dbType;
    }

    /**
     * @param string|null $dbType
     */
    public function setDbType(?string $dbType): void
    {
        $this->dbType = $dbType;
    }
}
