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

namespace PSB\PsbFoundation\Utility\Backend;

use PSB\PsbFoundation\Utility\ObjectUtility;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class IconUtility
 *
 * @package PSB\PsbFoundation\Utility\Backend
 */
class IconUtility
{
    /**
     * For use in ext_tables.php files
     *
     * @param string $extensionKey
     * @param array  $iconNames
     * @param string $path
     *
     * @throws Exception
     */
    public static function registerIconsFromExtensionDirectory(
        string $extensionKey,
        array $iconNames = [],
        string $path = 'Resources/Public/Icons'
    ): void {
        $path = GeneralUtility::getFileAbsFileName('EXT:' . $extensionKey . '/' . trim($path, '/'));
        $iconFiles = GeneralUtility::getFilesInDir($path, 'svg', true, '', 'Extension.*');

        if (is_iterable($iconFiles)) {
            $iconRegistry = ObjectUtility::get(IconRegistry::class);

            foreach ($iconFiles as $iconFile) {
                $fileName = pathinfo($iconFile, PATHINFO_FILENAME);

                if ([] === $iconNames || in_array($fileName, $iconNames, true)) {
                    $iconRegistry->registerIcon(
                        $extensionKey . '-' . str_replace('_', '-',
                            GeneralUtility::camelCaseToLowerCaseUnderscored($fileName)),
                        SvgIconProvider::class,
                        ['source' => $iconFile]
                    );
                }
            }
        }
    }
}
