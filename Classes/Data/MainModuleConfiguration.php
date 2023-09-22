<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Data;

/**
 * Class MainModuleConfiguration
 *
 * @package PSB\PsbFoundation\Data
 * @see     https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ExtensionArchitecture/HowTo/BackendModule/ModuleConfiguration.html
 */
class MainModuleConfiguration
{
    /**
     * @param string      $key
     * @param string|null $iconIdentifier      https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ExtensionArchitecture/HowTo/BackendModule/ModuleConfiguration.html#confval-iconIdentifier
     * @param string|null $labels              https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ExtensionArchitecture/HowTo/BackendModule/ModuleConfiguration.html#confval-labels
     * @param string|null $navigationComponent https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ExtensionArchitecture/HowTo/BackendModule/ModuleConfiguration.html#confval-navigationComponent
     * @param array|null  $position            https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ExtensionArchitecture/HowTo/BackendModule/ModuleConfiguration.html#confval-position
     * @param bool        $renderInModuleMenu  https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ExtensionArchitecture/HowTo/BackendModule/ModuleConfiguration.html#confval-position
     * @param string|null $workspaces          https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ExtensionArchitecture/HowTo/BackendModule/ModuleConfiguration.html#confval-appearance.renderInModuleMenu
     */
    public function __construct(
        protected string  $key,
        protected ?string $iconIdentifier = null,
        protected ?string $labels = null,
        protected ?string $navigationComponent = null,
        protected ?array  $position = null,
        protected bool    $renderInModuleMenu = true,
        protected ?string $workspaces = null,
    ) {
    }

    /**
     * @return string|null
     */
    public function getIconIdentifier(): ?string
    {
        return $this->iconIdentifier;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string|null
     */
    public function getLabels(): ?string
    {
        return $this->labels;
    }

    /**
     * @return string|null
     */
    public function getNavigationComponent(): ?string
    {
        return $this->navigationComponent;
    }

    /**
     * @return array|null
     */
    public function getPosition(): ?array
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function getRenderInModuleMenu(): bool
    {
        return $this->renderInModuleMenu;
    }

    /**
     * @return string|null
     */
    public function getWorkspaces(): ?string
    {
        return $this->workspaces;
    }
}
