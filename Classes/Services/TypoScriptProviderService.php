<?php

namespace PS\PsFoundation\Services;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class TypoScriptProviderService
 * @package PS\PsFoundation\Services
 */
class TypoScriptProviderService
{

    /**
     * @param string $configurationType
     * @param string|null $extensionName
     * @param string|null $pluginName
     *
     * @return array|null
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public static function getTypoScriptConfiguration(
        string $configurationType = ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
        string $extensionName = null,
        string $pluginName = null
    ): ?array {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $configurationManager = $objectManager->get(ConfigurationManager::class);
        $typoScript = $configurationManager->getConfiguration($configurationType, $extensionName, $pluginName);

        if (!\is_array($typoScript)) {
            return null;
        }

        $typoScriptService = $objectManager->get(\TYPO3\CMS\Core\TypoScript\TypoScriptService::class);

        return $typoScriptService->convertTypoScriptArrayToPlainArray($typoScript);
    }

}
