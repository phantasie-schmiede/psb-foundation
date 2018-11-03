<?php

namespace PS\PsFoundation\Services\DocComment\ValueParsers;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018 Daniel Ablass <dn@phantasie-schmiede.de>, Phantasie-Schmiede
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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class TcaConfigParser
 * @package PS\PsFoundation\Services\DocComment\ValueParsers
 */
class TcaConfigParser implements ValueParserInterface
{
    public const ANNOTATION_TYPE = '\PS\PsFoundation\Tca\Config';

    /**
     * @param null|string $value
     *
     * @return mixed
     * @throws Exception
     */
    public function processValue(?string $value)
    {
        if (null === $value) {
            throw new Exception('Annotation '.self::ANNOTATION_TYPE.' must contain a value!');
        }

        $result = [];
        $valueParts = GeneralUtility::trimExplode(',', $value, true);

        foreach ($valueParts as $part) {
            [$key, $value] = GeneralUtility::trimExplode('=', $part, false, 2);
            $result[$key] = $value;
        }

        return $result;
    }
}
