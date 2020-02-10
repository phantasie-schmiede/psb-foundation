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
 * Class PluginParser
 *
 * Use this annotation for methods in a plugin controller. It has to be followed by at least one keyword as defined in
 * FLAGS.
 *
 * @package PSB\PsbFoundation\Service\DocComment\ValueParsers
 */
class PluginActionParser extends AbstractFlagsParser
{
    public const ANNOTATION_TYPE = 'PSB\PsbFoundation\Plugin\Action';
    public const FLAGS           = [
        // the default action of the controller (executed, when no specific action is given in a request)
        'DEFAULT'  => 'default',
        // don't add this action to the list of allowed actions for the plugin
        'IGNORE'   => 'ignore',
        // add this action to list of uncached actions
        'UNCACHED' => 'uncached',
    ];
}
