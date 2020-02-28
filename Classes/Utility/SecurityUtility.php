<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Utility;

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

use TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException;

/**
 * Class SecurityUtility
 *
 * The class \TYPO3\CMS\Extbase\Security\Cryptography\HashService offers almost the same functionality, but sticks to
 * the rather vulnerable sha1 algorithm.
 *
 * @package PSB\PsbFoundation\Utility
 */
class SecurityUtility
{
    /**
     * @return string
     * @throws InvalidArgumentForHashGenerationException
     */
    public static function getEncryptionKey(): string
    {
        $encryptionKey = (string)$GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];

        if (!$encryptionKey) {
            throw new InvalidArgumentForHashGenerationException(__CLASS__ . ': There is no encryption key defined! Go to "Settings->Configure Installation-Wide Options" to set one.',
                1582894686);
        }

        return $encryptionKey;
    }

    /**
     * @param string $string
     * @param string $algorithm
     *
     * @return string
     * @throws InvalidArgumentForHashGenerationException
     */
    public static function generateHash(string $string, string $algorithm = 'sha256'): string
    {
        return hash_hmac($algorithm, $string, self::getEncryptionKey());
    }
}
