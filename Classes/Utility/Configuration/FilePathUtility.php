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

namespace PSB\PsbFoundation\Utility\Configuration;

use TYPO3\CMS\Core\Core\Environment;

/**
 * Class FilePathUtility
 *
 * @package PSB\PsbFoundation\Utility\Configuration
 */
class FilePathUtility
{
    /**
     * For use in php-files located in EXT:extension_key/Configuration/.
     *
     * @param string|null $filename custom filename without extension (.xlf is added automatically)
     *
     * @return string
     */
    public static function getLanguageFilePath(string $filename = null): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $callingFile = $trace[0]['file'];
        $shortPath = str_replace(Environment::getExtensionsPath() . '/', 'LLL:EXT:', $callingFile);
        $pathParts = explode('/', $shortPath);
        $extensionPath = array_shift($pathParts);
        $callingFilename = array_pop($pathParts);
        $filename = ($filename ?? str_replace('.php', '', $callingFilename)) . '.xlf:';

        return $extensionPath . '/Resources/Private/Language/Backend/' . implode('/', $pathParts) . '/' . lcfirst($filename);
    }
}
