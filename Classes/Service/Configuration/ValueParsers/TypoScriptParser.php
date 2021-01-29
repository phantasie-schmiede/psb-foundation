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

namespace PSB\PsbFoundation\Service\Configuration\ValueParsers;

use Exception;
use InvalidArgumentException;
use PSB\PsbFoundation\Service\TypoScriptProviderService;

/**
 * Class TypoScriptParser
 *
 * This parser allows to dynamically inject TypoScript values into a string, which can especially be useful for
 * FlexForms. Example:
 * 'Your TypoScript value is: ###PSB:TS:your.typoscript.value###'
 *
 * @package PSB\PsbFoundation\Service\Configuration\ValueParsers
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
            throw new InvalidArgumentException(self::class . ': Marker ' . self::MARKER_TYPE . ' must be followed by a valid TypoScript path!',
                1547210715);
        }

        return $typoScript;
    }
}
