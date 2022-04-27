<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Annotation;

use PSB\PsbFoundation\Utility\TypoScript\PageObjectConfiguration;
use PSB\PsbFoundation\Utility\ValidationUtility;

/**
 * Class PageType
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation
 */
class PageType extends AbstractAnnotation
{
    /**
     * @var bool
     */
    protected bool $cacheable = false;

    /**
     * Has to be a value of PageObjectConfiguration::CONTENT_TYPES.
     *
     * @var string
     */
    protected string $contentType;

    /**
     * @var bool
     */
    protected bool $disableAllHeaderCode = true;

    /**
     * @var int
     */
    protected int $typeNum;

    /**
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @return int
     */
    public function getTypeNum(): int
    {
        return $this->typeNum;
    }

    /**
     * @return bool
     */
    public function isCacheable(): bool
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
     * @param string $contentType
     *
     * @return void
     */
    public function setContentType(string $contentType): void
    {
        ValidationUtility::checkValueAgainstConstant(PageObjectConfiguration::CONTENT_TYPES, $contentType);
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
     * @param int $typeNum
     *
     * @return void
     */
    public function setTypeNum(int $typeNum): void
    {
        $this->typeNum = $typeNum;
    }
}
