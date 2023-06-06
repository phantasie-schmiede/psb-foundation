<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Utility\Configuration;

use PSB\PsbFoundation\Data\ExtensionInformationInterface;
use PSB\PsbFoundation\Utility\ArrayUtility;
use TYPO3\CMS\Core\Resource\Exception\InvalidFileException;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Class FilePathUtility
 *
 * @package PSB\PsbFoundation\Utility\Configuration
 */
class FilePathUtility
{
    public const EXTENSION_DIRECTORY_PREFIX = 'EXT:';
    public const LANGUAGE_LABEL_PREFIX      = 'LLL:';

    /**
     * @param ExtensionInformationInterface $extensionInformation
     *
     * @return string
     */
    public static function getLanguageFilePath(ExtensionInformationInterface $extensionInformation): string
    {
        return self::LANGUAGE_LABEL_PREFIX . self::getResourcePath($extensionInformation) . 'Private/Language/';
    }

    /**
     * For use in php-files located in an extension directory.
     *
     * This function generates the corresponding prefix for backend labels.
     * Example: Calling from EXT:my_extension/Configuration/TCA/Overrides/tt_content.xlf without submitting
     * a filename will return
     * "LLL:EXT:my_extension/Resources/Private/Language/Backend/Configuration/TCA/Overrides/tt_content.xlf".
     *
     * @param ExtensionInformationInterface $extensionInformation
     * @param string|null                   $filename custom filename without extension (.xlf is added automatically)
     *
     * @return string
     */
    public static function getLanguageFilePathForCurrentFile(
        ExtensionInformationInterface $extensionInformation,
        string                        $filename = null
    ): string {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $callingFilePathElements = explode('/', $trace[0]['file']);
        $indexOfExtensionKey = ArrayUtility::findLastOccurrence($extensionInformation->getExtensionKey(),
            $callingFilePathElements);
        $relativeFilePathElements = array_slice($callingFilePathElements, $indexOfExtensionKey);
        $callingFilename = array_pop($relativeFilePathElements);
        $filename = ($filename ?? str_replace('.php', '', $callingFilename)) . '.xlf:';

        return self::getLanguageFilePath($extensionInformation) . implode('/',
                $relativeFilePathElements) . '/' . lcfirst($filename);
    }

    /**
     * @param ExtensionInformationInterface $extensionInformation
     * @param string                        $subdirectory
     *
     * @return string
     */
    public static function getPrivateResourcePath(ExtensionInformationInterface $extensionInformation, string $subdirectory = ''): string
    {
        $directoryPath = self::getResourcePath($extensionInformation, 'Private/' . ltrim('/', $subdirectory));

        return self::EXTENSION_DIRECTORY_PREFIX . $directoryPath;
    }

    /**
     * @param ExtensionInformationInterface $extensionInformation
     * @param string                        $subdirectory
     *
     * @return string
     */
    public static function getPublicResourcePath(ExtensionInformationInterface $extensionInformation, string $subdirectory = ''): string
    {
        $directoryPath = self::getResourcePath($extensionInformation, 'Public/' . ltrim('/', $subdirectory));

        return self::EXTENSION_DIRECTORY_PREFIX . $directoryPath;
    }

    /**
     * @param ExtensionInformationInterface $extensionInformation
     * @param string                        $subdirectory
     *
     * @return string
     * @throws InvalidFileException
     */
    public static function getPublicResourceWebPath(ExtensionInformationInterface $extensionInformation, string $subdirectory = ''): string
    {
        $directoryPath = self::getPublicResourcePath($extensionInformation, $subdirectory);

        return PathUtility::getPublicResourceWebPath($directoryPath);
    }

    /**
     * @param ExtensionInformationInterface $extensionInformation
     * @param string                        $subdirectory
     *
     * @return string
     */
    public static function getResourcePath(ExtensionInformationInterface $extensionInformation, string $subdirectory = ''): string
    {
        $directoryPath = $extensionInformation->getExtensionKey() . '/Resources/' . ltrim('/', $subdirectory);

        return self::EXTENSION_DIRECTORY_PREFIX . $directoryPath;
    }
}
