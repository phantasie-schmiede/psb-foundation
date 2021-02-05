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

namespace PSB\PsbFoundation\Service\Configuration;

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class IconService
 *
 * @package PSB\PsbFoundation\Service\Configuration
 */
class IconService
{
    /**
     * For use in ext_localconf.php files
     *
     * @param string $extensionKey
     * @param array  $iconNames
     * @param string $path
     */
    public function registerIconsFromExtensionDirectory(
        string $extensionKey,
        array $iconNames = [],
        string $path = 'Resources/Public/Icons'
    ): void {
        $path = GeneralUtility::getFileAbsFileName('EXT:' . $extensionKey . '/' . trim($path, '/'));
        $iconFiles = GeneralUtility::getFilesInDir($path, 'svg', true, '', 'Extension.*');
        $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);

        if (is_iterable($iconFiles)) {
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
