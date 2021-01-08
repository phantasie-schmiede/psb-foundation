<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Data;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019-2020 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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

/**
 * Class AbstractExtensionInformation
 *
 * You only need to extend this class to be able to provide the extension_key, the ExtensionName and the VendorName.
 * This information is extracted within the constructor using the namespaced class name of your class. If you want to
 * use module or plugin services, make sure to overwrite the constants MODULES and PLUGINS with your own information
 * (see examples below).
 *
 * @package PSB\PsbFoundation\Data
 */
abstract class AbstractExtensionInformation implements ExtensionInformationInterface
{
    /**
     * This may be overridden in extending class. It gets evaluated in
     * psb_foundation/Configuration/Extbase/Persistence/Classes.php
     */
    public const MAPPING = [
        // 'VENDOR\MyExtension\Domain\Model\FrontendUser' => 'fe_users',
    ];

    /**
     * may be overridden in extending class
     */
    public const MODULES = [
        // 'submoduleKey' => [\Your\Module\Controller::class, \Your\Module\AnotherController::class],
    ];

    /**
     * may be overridden in extending class
     *
     * The keys (doktype) have to be of type integer. Name is the only mandatory value.
     * If you don't provide an icon identifier this default identifier will be used:
     * pageType-yourPageTypeName
     * In each case your svg-file needs to be located in this directory:
     * EXT:your_extension/Resources/Public/Icons/
     * All icons in that directory will be registered by their name automatically.
     *
     * Unless "label" is defined,
     * EXT:your_extension/Resources/Private/Language/Backend/Configuration/TCA/Overrides/pages.xlf:pageType.yourPageTypeName
     * will be used. If that key doesn't exist, "name" will be transformed from "yourPageTypeName" to
     * "Your page type name".
     */
    public const PAGE_TYPES = [
        /*
         * doktype => [
         *     'allowedTables' => ['*'],
         *     'iconIdentifier' => 'pageType-yourPageTypeName'
         *     'label' => 'Your page type name'
         *     'name' => 'yourPageTypeName',
         * ],
         */
    ];

    /**
     * may be overridden in extending class
     */
    public const PLUGINS = [
        // 'pluginName' => [\Your\Plugin\Controller::class, \Your\Plugin\AnotherController::class],
    ];

    /**
     * @var string
     */
    protected string $extensionKey;

    /**
     * @var string
     */
    protected string $extensionName;

    /**
     * @var string
     */
    protected string $vendorName;

    public function __construct()
    {
        [$this->vendorName, $this->extensionName] = explode('\\', get_class($this));
        $this->extensionKey = GeneralUtility::camelCaseToLowerCaseUnderscored($this->extensionName);
    }

    /**
     * @return string
     */
    public function getExtensionKey(): string
    {
        return $this->extensionKey;
    }

    /**
     * @return string
     */
    public function getExtensionName(): string
    {
        return $this->extensionName;
    }

    /**
     * @return array
     */
    public function getMapping(): array
    {
        return static::MAPPING;
    }

    /**
     * @return array
     */
    public function getModules(): array
    {
        return static::MODULES;
    }

    /**
     * @return array
     */
    public function getPageTypes(): array
    {
        return static::PAGE_TYPES;
    }

    /**
     * @return array
     */
    public function getPlugins(): array
    {
        return static::PLUGINS;
    }

    /**
     * @return string
     */
    public function getVendorName(): string
    {
        return $this->vendorName;
    }
}
