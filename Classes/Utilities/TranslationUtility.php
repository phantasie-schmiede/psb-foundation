<?php
declare(strict_types=1);

namespace PSB\PsbFoundation\Utilities;

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
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class TranslationUtility
 * @package PSB\PsbFoundation\Utilities
 */
class TranslationUtility
{
    /**
     * @param string      $key
     * @param null|string $extension
     *
     * @return string
     */
    public static function translatePreservingNewLines(string $key, string $extension = null): string
    {
        $translation = LocalizationUtility::translate($key, $extension);
        // split string by linebreaks and remove surrounding whitespaces for each line
        $lines = array_map('trim', explode("\n", $translation));
        // remove first and/or last element if they are empty
        if ('' === $lines[0]) {
            array_shift($lines);
        }
        if ('' === array_values(\array_slice($lines, -1))[0]) {
            array_pop($lines);
        }

        return implode("\n", $lines);
    }

    /**
     * @param string      $key
     * @param null|string $extension
     * @param string      $newLineMarker If set, user defined new lines are created while plain line breaks will still
     *                                   be removed
     *
     * @return string
     */
    public static function translateConcatenatingNewLines(
        string $key,
        string $extension = null,
        string $newLineMarker = '||'
    ): string {
        $translation = LocalizationUtility::translate($key, $extension);
        $translation = preg_replace('/\s+/', ' ', $translation);
        if ('' !== $newLineMarker) {
            $translation = str_replace($newLineMarker, "\n", $translation);
        }

        return $translation;
    }
}
