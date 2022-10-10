<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Attribute\TCA\ColumnType;

/**
 * Class File
 *
 * @package PSB\PsbFoundation\Attribute\TCA\ColumnType
 */
class File extends AbstractColumnType
{
    /**
     * @param array|string $allowed          https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/File/Properties/Allowed.html
     * @param int|null     $maxitems         https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/File/Properties/Maxitems.html
     * @param int|null     $minitems         https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/File/Properties/Minitems.html
     * @param array|null   $overrideChildTca https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/File/Properties/OverrideChildTCa.html
     */
    public function __construct(
        protected array|string $allowed = 'common-image-types',
        protected ?int $maxitems = null,
        protected ?int $minitems = null,
        protected ?array $overrideChildTca = null,
    ) {
    }

    /**
     * @return array|string
     */
    public function getAllowed(): array|string
    {
        return $this->allowed;
    }

    /**
     * @return int|null
     */
    public function getMaxitems(): ?int
    {
        return $this->maxitems;
    }

    /**
     * @return int|null
     */
    public function getMinitems(): ?int
    {
        return $this->minitems;
    }

    /**
     * @return array|null
     */
    public function getOverrideChildTca(): ?array
    {
        return $this->overrideChildTca;
    }
}
