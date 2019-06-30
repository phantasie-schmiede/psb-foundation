<?php
declare(strict_types=1);

namespace PSB\PsbFoundation\Services\Configuration\ValueParsers;

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

use Exception;
use InvalidArgumentException;
use PSB\PsbFoundation\Services\TypoScriptProviderService;

/**
 * Class TypoScriptParser
 *
 * This parser allows to dynamically inject TypoScript values into a string, which can especially be useful for
 * FlexForms. Example:
 * 'Your TypoScript value is: ###PSB:TS:your.typoscript.value###'
 *
 * @package PSB\PsbFoundation\Services\Configuration\ValueParsers
 */
class TypoScriptParser implements ValueParserInterface
{
    public const MARKER_TYPE = 'PSB:TS';

    /**
     * @param string|null $value
     *
     * @return mixed
     * @throws Exception
     */
    public function processValue(?string $value)
    {
        try {
            $typoScript = TypoScriptProviderService::getTypoScriptConfiguration($value);
        } catch (Exception $e) {
            throw new InvalidArgumentException(self::class.': Marker '.self::MARKER_TYPE.' must be followed by a valid TypoScript path!',
                1547210715);
        }

        return $typoScript;
    }
}
