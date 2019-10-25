<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Utility\TypoScript;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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

/**
 * Class PageObjectConfiguration
 * @package PSB\PsbFoundation\Utility\TypoScript
 */
class PageObjectConfiguration
{
    public const CONTENT_TYPES = [
        'HTML' => 'text/html',
        'XML'  => 'text/xml',
    ];

    /**
     * @var string
     */
    protected $action;

    /**
     * @var string
     */
    protected $contentType = self::CONTENT_TYPES['HTML'];

    /**
     * @var string
     */
    protected $controller;

    /**
     * @var string
     */
    protected $extensionName;

    /**
     * @var string
     */
    protected $pluginName;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var int
     */
    protected $typeNum;

    /**
     * @var string
     */
    protected $typoScriptObjectName;

    /**
     * @var string
     */
    protected $vendorName;

    /**
     * @return string
     */
    public function getAction(): string
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
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * @return string
     */
    public function getController(): string
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
     * @return string
     */
    public function getExtensionName(): string
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
     * @return string
     */
    public function getPluginName(): string
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
     * @return int|string
     */
    public function getTypeNum()
    {
        return $this->typeNum;
    }

    /**
     * @param int|string $typeNum
     *
     * @return $this
     */
    public function setTypeNum($typeNum): self
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
     * @return string
     */
    public function getVendorName(): string
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
}
