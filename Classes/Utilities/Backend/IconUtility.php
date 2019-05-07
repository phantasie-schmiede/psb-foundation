<?php
declare(strict_types=1);

namespace PS\PsFoundation\Utilities\Backend;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 PSG Web Team <webdev@plan.de>, PSG Plan Service Gesellschaft mbH
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

use PS\PsFoundation\Traits\StaticInjectionTrait;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class IconUtility
 * @package PS\PsFoundation\Utilities\Backend
 */
class IconUtility
{
    use StaticInjectionTrait;

    /**
     * For use in ext_tables.php
     *
     * @param string $extensionKey
     * @param array  $iconNames
     * @param string $path
     */
    public static function registerIconsFromExtensionDirectory(
        string $extensionKey,
        array $iconNames = [],
        string $path = 'Resources/Public/Icons'
    ): void {
        $path = GeneralUtility::getFileAbsFileName('EXT:'.$extensionKey.'/'.trim($path, '/'));
        $iconFiles = GeneralUtility::getFilesInDir($path, 'svg', true, '', 'ext_icon.*');

        if (is_iterable($iconFiles)) {
            $iconRegistry = self::get(IconRegistry::class);

            foreach ($iconFiles as $iconFile) {
                $filename = pathinfo($iconFile, PATHINFO_FILENAME);

                if ([] === $iconNames || in_array($filename, $iconNames, true)) {
                    $iconRegistry->registerIcon(
                        $filename,
                        SvgIconProvider::class,
                        ['source' => $iconFile]
                    );
                }
            }
        }
    }
}
