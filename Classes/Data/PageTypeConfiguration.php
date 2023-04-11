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
 * Class PageTypeConfiguration
 *
 * @package PSB\PsbFoundation\Data
 */
class PageTypeConfiguration
{
    /**
     * @param int         $doktype
     * @param string      $name
     * @param array       $allowedTables  If empty, all tables are allowed.
     * @param string|null $iconIdentifier Defaults to page-type-[your-page-type-name] if not set.
     * @param string|null $label          Defaults to
     *                                    EXT:[your_extension]/Resources/Private/Language/Backend/Configuration/TCA/Overrides/page.xlf:pageType.[yourPageTypeName]
     *                                    if not set. If that key doesn't exist, "name" will be transformed from
     *                                    "yourPageTypeName" to "Your page type name".
     */
    public function __construct(
        protected int $doktype,
        protected string $name,
        protected array $allowedTables = [],
        protected ?string $iconIdentifier = null,
        protected ?string $label = null,
    ) {
    }

    /**
     * @return array
     */
    public function getAllowedTables(): array
    {
        return $this->allowedTables;
    }

    /**
     * @return int
     */
    public function getDoktype(): int
    {
        return $this->doktype;
    }

    /**
     * @return string|null
     */
    public function getIconIdentifier(): ?string
    {
        return $this->iconIdentifier;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
