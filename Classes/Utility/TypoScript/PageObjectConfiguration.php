<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Utility\TypoScript;

use InvalidArgumentException;
use PSB\PsbFoundation\Enum\ContentType;
use PSB\PsbFoundation\Utility\StringUtility;
use PSB\PsbFoundation\Utility\ValidationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use function array_slice;
use function count;

/**
 * Class PageObjectConfiguration
 *
 * @package PSB\PsbFoundation\Utility\TypoScript
 */
class PageObjectConfiguration
{
    /**
     * @var bool
     */
    protected bool $cacheable;

    /**
     * @var ContentType
     */
    protected ContentType $contentType = ContentType::HTML;

    /**
     * @var bool
     */
    protected bool $disableAllHeaderCode = true;

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
     * @return ContentType
     */
    public function getContentType(): ContentType
    {
        return $this->contentType;
    }

    /**
     * @return string|null
     */
    public function getExtensionName(): ?string
    {
        return $this->extensionName;
    }

    /**
     * @return string|null
     */
    public function getPluginName(): ?string
    {
        return $this->pluginName;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @return int
     */
    public function getTypeNum(): int
    {
        return $this->typeNum;
    }

    /**
     * @return string
     */
    public function getTypoScriptObjectName(): string
    {
        return $this->typoScriptObjectName;
    }

    /**
     * @return string|null
     */
    public function getUserFunc(): ?string
    {
        return $this->userFunc;
    }

    /**
     * @return array
     */
    public function getUserFuncParameters(): array
    {
        return $this->userFuncParameters ?? [];
    }

    /**
     * @return string|null
     */
    public function getVendorName(): ?string
    {
        return $this->vendorName;
    }

    /**
     * @return bool|null
     */
    public function isCacheable(): ?bool
    {
        return $this->cacheable;
    }

    /**
     * @return bool
     */
    public function isDisableAllHeaderCode(): bool
    {
        return $this->disableAllHeaderCode;
    }

    /**
     * @param bool $cacheable
     *
     * @return void
     */
    public function setCacheable(bool $cacheable): void
    {
        $this->cacheable = $cacheable;
    }

    /**
     * @param ContentType $contentType
     */
    public function setContentType(ContentType $contentType): void
    {
        $this->contentType = $contentType;
    }

    /**
     * @param bool $disableAllHeaderCode
     *
     * @return void
     */
    public function setDisableAllHeaderCode(bool $disableAllHeaderCode): void
    {
        $this->disableAllHeaderCode = $disableAllHeaderCode;
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
     * @param string $userFunc
     *
     * @return void
     */
    public function setUserFunc(string $userFunc): void
    {
        $this->userFunc = $userFunc;
    }

    /**
     * @param array $userFuncParameters
     *
     * @return void
     */
    public function setUserFuncParameters(array $userFuncParameters): void
    {
        $this->userFuncParameters = $userFuncParameters;
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
