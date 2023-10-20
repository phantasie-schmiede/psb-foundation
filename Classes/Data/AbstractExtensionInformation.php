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
use function get_class;

/**
 * Class AbstractExtensionInformation
 *
 * You only need to extend this class to be able to provide the extension_key, the ExtensionName and the VendorName.
 * This information is extracted within the constructor using the namespaced class name of your class. If you want to
 * use module, plugin or page type services, make sure to overwrite the constants MODULES and PLUGINS with your own
 * information
 * (see examples below).
 *
 * @package PSB\PsbFoundation\Data
 */
abstract class AbstractExtensionInformation implements ExtensionInformationInterface
{
    /**
     * @var string
     */
    private string $extensionKey;

    /**
     * @var string
     */
    private string $extensionName;

    /**
     * @var MainModuleConfiguration[]
     */
    private array $mainModules = [];

    /**
     * @var ModuleConfiguration[]
     */
    private array $modules = [];

    /**
     * @var PageTypeConfiguration[]
     */
    private array $pageTypes = [];

    /**
     * @var PluginConfiguration[]
     */
    private array $plugins = [];

    /**
     * @var string
     */
    private string $vendorName;

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
     * @return MainModuleConfiguration[]
     */
    public function getMainModules(): array
    {
        return $this->mainModules;
    }

    /**
     * @return ModuleConfiguration[]
     */
    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * @return PageTypeConfiguration[]
     */
    public function getPageTypes(): array
    {
        return $this->pageTypes;
    }

    /**
     * @return PluginConfiguration[]
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    /**
     * @return string
     */
    public function getVendorName(): string
    {
        return $this->vendorName;
    }

    /**
     * @param MainModuleConfiguration $configuration
     *
     * @return $this
     */
    protected function addMainModule(MainModuleConfiguration $configuration): static
    {
        $this->mainModules[] = $configuration;

        return $this;
    }

    /**
     * @param ModuleConfiguration $configuration
     *
     * @return $this
     */
    protected function addModule(ModuleConfiguration $configuration): static
    {
        $this->modules[] = $configuration;

        return $this;
    }

    /**
     * @param PageTypeConfiguration $configuration
     *
     * @return $this
     */
    protected function addPageType(PageTypeConfiguration $configuration): static
    {
        $this->pageTypes[] = $configuration;

        return $this;
    }

    /**
     * @param PluginConfiguration $configuration
     *
     * @return $this
     */
    protected function addPlugin(PluginConfiguration $configuration): static
    {
        $this->plugins[] = $configuration;

        return $this;
    }

    /**
     * @return string
     */
    protected function buildModuleKeyPrefix(): string
    {
        return strtolower(str_replace('_', '', $this->extensionKey)) . '_';
    }
}
