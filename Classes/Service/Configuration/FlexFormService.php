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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class FlexFormService
 *
 * @package PSB\PsbFoundation\Service\Configuration
 */
class FlexFormService
{
    public const ALL_PLUGINS = '*';

    /**
     * @param string $xml             Pass the raw XML-data, not the file path!
     * @param string $pluginSignature '*' if you add a FlexForm for a content element, otherwise:
     *                                '[extensionkey]_[pluginname]'
     * @param string $cType           Plugins use the default value ('list').
     *
     * @return void
     */
    public function register(string $xml, string $pluginSignature = self::ALL_PLUGINS, string $cType = 'list'): void
    {
        if (self::ALL_PLUGINS !== $pluginSignature) {
            $pluginSignature = strtolower($pluginSignature);
            $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
        }

        ExtensionManagementUtility::addPiFlexFormValue(
            $pluginSignature,
            $xml,
            $cType
        );
    }
}
