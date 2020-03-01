<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Utility\TypoScript;

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

use PSB\PsbFoundation\Utility\ValidationUtility;

/**
 * Class PageObjectConfiguration
 * @package PSB\PsbFoundation\Utility\TypoScript
 */
class PageObjectConfiguration
{
    public const CONTENT_TYPES = [
        'HTML' => 'text/html',
        'JSON' => 'application/json',
        'XML'  => 'text/xml',
    ];

    /**
     * @var string
     */
    protected string $action;

    /**
     * @var bool
     */
    protected bool $cacheable;

    /**
     * @var string
     */
    protected string $contentType = self::CONTENT_TYPES['HTML'];

    /**
     * @var string
     */
    protected string $controller;

    /**
     * @var string
     */
    protected string $extensionName;

    /**
     * @var string
     */
    protected string $pluginName;

    /**
     * @var array
     */
    protected array $settings = [];

    /**
     * @var int
     */
    protected int $typeNum;

    /**
     * @var string
     */
    protected string $typoScriptObjectName = '';

    /**
     * @var string
     */
    protected string $userFunc = '';

    /**
     * @var array
     */
    protected array $userFuncParameters;

    /**
     * @var string
     */
    protected string $vendorName;

    /**
     * @return string|null
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * @param string $action
     *
     * @return $this
     */
    public function setAction(string $action): self
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @param string $contentType
     *
     * @return $this
     */
    public function setContentType(string $contentType): self
    {
        ValidationUtility::checkValueAgainstConstant(self::CONTENT_TYPES, $contentType);
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getController(): ?string
    {
        return $this->controller;
    }

    /**
     * @param string $controller
     *
     * @return $this
     */
    public function setController(string $controller): self
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getExtensionName(): ?string
    {
        return $this->extensionName;
    }

    /**
     * @param string $extensionName
     *
     * @return $this
     */
    public function setExtensionName(string $extensionName): self
    {
        $this->extensionName = $extensionName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPluginName(): ?string
    {
        return $this->pluginName;
    }

    /**
     * @param string $pluginName
     *
     * @return $this
     */
    public function setPluginName(string $pluginName): self
    {
        $this->pluginName = $pluginName;

        return $this;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     *
     * @return $this
     */
    public function setSettings(array $settings): self
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * @return int
     */
    public function getTypeNum(): int
    {
        return $this->typeNum;
    }

    /**
     * @param int $typeNum
     *
     * @return $this
     */
    public function setTypeNum(int $typeNum): self
    {
        $this->typeNum = $typeNum;

        return $this;
    }

    /**
     * @return string
     */
    public function getTypoScriptObjectName(): string
    {
        return $this->typoScriptObjectName;
    }

    /**
     * @param string $typoScriptObjectName
     *
     * @return $this
     */
    public function setTypoScriptObjectName(string $typoScriptObjectName): self
    {
        $this->typoScriptObjectName = $typoScriptObjectName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUserFunc(): ?string
    {
        return $this->userFunc;
    }

    /**
     * @param string $userFunc
     */
    public function setUserFunc(string $userFunc): void
    {
        $this->userFunc = $userFunc;
    }

    /**
     * @return array
     */
    public function getUserFuncParameters(): array
    {
        return $this->userFuncParameters ?? [];
    }

    /**
     * @param array $userFuncParameters
     */
    public function setUserFuncParameters(array $userFuncParameters): void
    {
        $this->userFuncParameters = $userFuncParameters;
    }

    /**
     * @return string|null
     */
    public function getVendorName(): ?string
    {
        return $this->vendorName;
    }

    /**
     * @param string $vendorName
     *
     * @return $this
     */
    public function setVendorName(string $vendorName): self
    {
        $this->vendorName = $vendorName;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isCacheable(): ?bool
    {
        return $this->cacheable;
    }

    /**
     * @param bool $cacheable
     */
    public function setCacheable(bool $cacheable): void
    {
        $this->cacheable = $cacheable;
    }
}
