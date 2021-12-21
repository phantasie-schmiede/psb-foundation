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

namespace PSB\PsbFoundation\Library;

/**
 * Class Fields
 *
 * This helper class holds default configurations for a variety of field types. Use it to register fields in FlexForms
 * or TCA.
 *
 * @package PSB\PsbFoundation\Service\Configuration
 * @see     \PSB\PsbFoundation\Service\Configuration\TcaService
 */
class TcaFields
{
    public const TYPES = [
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
        'SLUG'        => 'slug',
        'TEXT'        => 'text',
        'TIME'        => 'input',
        'USER'        => 'user',
    ];
}
