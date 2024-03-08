<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Utility;

use NumberFormatter;
use RuntimeException;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use function is_int;
use function is_string;

/**
 * Class FileUtility
 *
 * @package PSB\PsbFoundation\Utility
 */
class FileUtility
{
    /**
     * Although calculated on a base of 2, the average user might be confused when he is shown the technically correct
     * unit names like KiB, MiB or GiB. Hence, the inaccurate, "old" units are being used.
     */
    public const FILE_SIZE_UNITS = [
        'B'  => 0,
        'KB' => 1,
        'MB' => 2,
        'GB' => 3,
        'TB' => 4,
        'PB' => 5,
        'EB' => 6,
        'ZB' => 7,
        'YB' => 8,
    ];

    public static function fileExists(string $filename): bool
    {
        return file_exists(self::resolveFileName($filename));
    }

    /**
     * Convert file size to a human-readable string.
     *
     * To enforce a specific unit use a value of FILE_SIZE_UNITS as second parameter.
     *
     * @param int|string $input You can pass either the filesize or the filename.
     * @param int|null   $unit
     * @param int        $decimals
     *
     * @return string
     * @throws AspectNotFoundException
     */
    public static function formatFileSize(
        int|string $input,
        int        $unit = null,
        int        $decimals = 2,
    ): string {
        switch (true) {
            case is_int($input):
                $bytes = $input;
                break;
            case is_string($input):
                $input = self::resolveFileName($input);
                $bytes = filesize($input);
                break;
            default:
                throw new RuntimeException(
                    __CLASS__ . ': Argument 1 of formatFileSize() has to be integer or string!', 1614368333
                );
        }

        if ($unit) {
            $bytes /= (1024 ** $unit);
        } else {
            $power = 0;

            while ($bytes >= 1024) {
                $bytes /= 1024;
                $power++;
            }
        }

        $unitString = array_search($power ?? $unit, self::FILE_SIZE_UNITS, true);
        $numberFormatter = StringUtility::getNumberFormatter();
        $numberFormatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);

        return $numberFormatter->format($bytes) . '&nbsp;' . $unitString;
    }

    /**
     * @param string $fileName
     *
     * @return false|string
     */
    public static function getMimeType(string $fileName): bool|string
    {
        $fileInformation = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = $fileInformation->file($fileName);
        finfo_close($fileInformation);

        return $mimeType;
    }

    /**
     * Converts relative to absolute paths.
     *
     * @param string $fileName
     *
     * @return string returns an empty string if $fileName could not be resolved to a valid path
     */
    public static function resolveFileName(string $fileName): string
    {
        return GeneralUtility::getFileAbsFileName($fileName) ?: realpath($fileName) ?: '';
    }

    public static function write(string $fileName, string $content, bool $append = false): bool
    {
        $fileName = self::resolveFileName($fileName);
        $pathDetails = pathinfo($fileName);

        // Directory creation is skipped if it already exists.
        GeneralUtility::mkdir_deep($pathDetails['dirname']);

        if (!@is_file($fileName)) {
            $changePermissions = true;
        }

        $success = (bool)file_put_contents($fileName, $content, $append ? FILE_APPEND : 0);

        if ($success && ($changePermissions ?? false)) {
            GeneralUtility::fixPermissions($fileName);
        }

        return $success;
    }
}
