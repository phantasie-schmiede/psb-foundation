<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Data;

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
     * may be overridden in extending class
     */
    public const MAIN_MODULES = [
        /*
        *  'mainModuleKey' => [
        *      'iconIdentifier' => '...', // optional
        *      'labels'         => '...', // optional
        *      'position'       => '...', // optional
        *      'routeTarget'    => '...', // optional
        *  ],
        */
    ];

    /**
     * may be overridden in extending class
     */
    public const MODULES = [
        /*
         *  'submoduleKey' => [
         *      \Your\Module\Controller::class,
         *      \Your\Module\AnotherController::class
         *  ],
         */
    ];

    /**
     * may be overridden in extending class
     *
     * The keys (doktype) have to be of type integer. Name is the only mandatory value.
     * If you don't provide an icon identifier this default identifier will be used:
     * page-type-your-page-type-name
     * In each case your SVG-file needs to be located in this directory:
     * EXT:your_extension/Resources/Public/Icons/
     * All icons in that directory will be registered by their name automatically.
     *
     * Unless "label" is defined,
     * EXT:your_extension/Resources/Private/Language/Backend/Configuration/TCA/Overrides/page.xlf:pageType.yourPageTypeName
     * will be used. If that key doesn't exist, "name" will be transformed from "yourPageTypeName" to
     * "Your page type name".
     */
    public const PAGE_TYPES = [
        /*
         * doktype => [
         *     'allowedTables'  => ['*'],                          // optional
         *     'iconIdentifier' => 'page-type-your-page-type-name' // optional
         *     'label'          => 'Your page type name'           // optional
         *     'name'           => 'yourPageTypeName',             // optional
         *     'type'           => 'web',                          // optional
         * ],
         */
    ];

    /**
     * may be overridden in extending class
     */
    public const PLUGINS = [
        /*
        *  'pluginName' => [
        *      \Your\Plugin\Controller::class,
        *      \Your\Plugin\AnotherController::class
        *  ],
        */
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
    public function getMainModules(): array
    {
        return static::MAIN_MODULES;
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
