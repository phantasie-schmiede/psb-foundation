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

namespace PSB\PsbFoundation\Utility;

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
