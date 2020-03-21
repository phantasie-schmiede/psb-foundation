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

use PSB\PsbFoundation\Service\Configuration\Fields;

/**
 * Class DateTime
 *
 * @Annotation
 * @package PSB\PsbFoundation\Service\DocComment\Annotations\TCA
 */
class DateTime extends Input
{
    public const TYPE = Fields::FIELD_TYPES['DATETIME'];

    /**
     * @var string
     */
    protected string $dbType = 'datetime';

    /**
     * @var string
     */
    protected string $eval = 'datetime,null';

    /**
     * @var string
     */
    protected string $renderType = 'inputDateTime';

    /**
     * @var int
     */
    protected int $size = 12;

    /**
     * @return string
     */
    public function getDbType(): string
    {
        return $this->dbType;
    }

    /**
     * @param string $dbType
     */
    public function setDbType(string $dbType): void
    {
        $this->dbType = $dbType;
    }
}
