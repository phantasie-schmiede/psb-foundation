<?php
declare(strict_types=1);

namespace PSB\PsbFoundation\Data;

use TYPO3\CMS\Core\SingletonInterface;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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

/**
 * Interface ExtensionInformationInterface
 * @package PSB\PsbFoundation\Data
 */
interface ExtensionInformationInterface
{
    /**
     * @return string
     */
    public function getExtensionKey(): string;

    /**
     * @return string
     */
    public function getExtensionName(): string;

    /**
     * @return array
     */
    public function getModules(): array;

    /**
     * @return array
     */
    public function getPlugins(): array;

    /**
     * @return string
     */
    public function getVendorName(): string;
}
