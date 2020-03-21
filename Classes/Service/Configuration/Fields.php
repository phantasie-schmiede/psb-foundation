<?php
declare(strict_types=1);

namespace PSB\PsbFoundation\Service\Configuration;

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

use InvalidArgumentException;
use function in_array;

/**
 * Class Fields
 *
 * This helper class holds default configurations for a variety of field types. Use it to register fields in FlexForms
 * or TCA.
 *
 * @package PSB\PsbFoundation\Service\Configuration
 * @see     \PSB\PsbFoundation\Service\Configuration\FlexFormService
 * @see     \PSB\PsbFoundation\Service\Configuration\TcaService
 */
class Fields
{
    public const FIELD_TYPES = [
        'CHECKBOX'    => 'check',
        'DATE'        => 'input',
        'DATETIME'    => 'input',
        'DOCUMENT'    => 'document',
        'FILE'        => 'file',
        'FLOAT'       => 'input',
        'GROUP'       => 'group',
        'IMAGE'       => 'image',
        'INLINE'      => 'inline',
        'INPUT'       => 'input',
        'INTEGER'     => 'input',
        'LINK'        => 'input',
        'MM'          => 'select',
        'PASSTHROUGH' => 'passthrough',
        'SELECT'      => 'select',
        'TEXT'        => 'text',
        'TIME'        => 'input',
        'USER'        => 'user',
    ];
}
