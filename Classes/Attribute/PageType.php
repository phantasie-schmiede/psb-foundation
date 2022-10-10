<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Attribute;

use Attribute;
use PSB\PsbFoundation\Utility\TypoScript\PageObjectConfiguration;
use PSB\PsbFoundation\Utility\ValidationUtility;

/**
 * Class PageType
 *
 * @package PSB\PsbFoundation\Attribute
 */
#[Attribute(Attribute::TARGET_METHOD)]
class PageType extends AbstractAttribute
{
    /**
     * @param bool   $cacheable
     * @param string $contentType
     * @param bool   $disableAllHeaderCode
     * @param int    $typeNum
     */
    public function __construct(
        /** Has to be a value of PageObjectConfiguration::CONTENT_TYPES. */
        protected string $contentType,
        protected int $typeNum,
        protected bool $cacheable = false,
        protected bool $disableAllHeaderCode = true,
    ) {
        ValidationUtility::checkValueAgainstConstant(PageObjectConfiguration::CONTENT_TYPES, $contentType);
    }

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
}
