<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Service\DocComment\ValueParsers;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019-2020 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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
 * Class TcaConfigParser
 *
 * Use this in the annotations of your domain model properties. Possible attributes are all those listed in the
 * official TCA documentation: https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Columns/Index.html EXCEPT
 * "config". To define those values use the TcaFieldConfigParser annotation.
 * Additional attributes:
 * editableInFrontend - if set to true, \PSB\PsbFoundation\ViewHelpers\Form\BuildFromTcaViewHelper can be used for
 *                      this domain model. If used in class annotation, this attribute applies to all properties
 *                      annotated with @PSB\PsbFoundation\Tca\FieldConfig. But it can also be set for each property
 *                      individually.
 *
 * @package PSB\PsbFoundation\Service\DocComment\ValueParsers
 */
class TcaConfigParser extends AbstractValuePairsParser
{
    public const ANNOTATION_TYPE = 'PSB\PsbFoundation\Tca\Config';

    public const ATTRIBUTES = [
        'EDITABLE_IN_FRONTEND' => 'editableInFrontend',
    ];
}
