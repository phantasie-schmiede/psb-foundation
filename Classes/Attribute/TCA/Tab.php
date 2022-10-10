<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Attribute\TCA;

use Attribute;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Tab
 *
 * @package PSB\PsbFoundation\Attribute\TCA
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
class Tab extends AbstractTcaAttribute
{
    /**
     * @param string      $identifier
     * @param string|null $label
     * @param string      $position
     */
    public function __construct(
        protected string $identifier = '',
        protected ?string $label = null,
        /**
         * Usage: 'key:propertyName'
         * You can use the keys 'after', 'before' and 'replace'.
         */
        protected string $position = '',
    ) {
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
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
    public function getPosition(): string
    {
        if (empty($this->position)) {
            return '';
        }

        [$key, $location] = GeneralUtility::trimExplode(':', $this->position, false, 2);

        // Check if $location is NOT a palette name.
        if (!str_contains($location, '-')) {
            $location = $this->tcaService->convertPropertyNameToColumnName($location);
        }

        return $key . ':' . $location;
    }
}
