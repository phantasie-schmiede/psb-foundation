<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Annotation\TCA;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Tab
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA
 */
class Tab extends AbstractTcaAnnotation
{
    /**
     * @var string
     */
    protected string $identifier = '';

    /**
     * @var string|null
     */
    protected ?string $label = null;

    /**
     * Usage: 'key:propertyName'
     * You can use the keys 'after', 'before' and 'replace'.
     *
     * @var string
     */
    protected string $position = '';

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
        if (false === mb_strpos($location, '-')) {
            $location = $this->tcaService->convertPropertyNameToColumnName($location);
        }

        return $key . ':' . $location;
    }

    /**
     * @param string $identifier
     *
     * @return void
     */
    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    /**
     * @param string|null $label
     *
     * @return void
     */
    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    /**
     * @param string $position
     *
     * @return void
     */
    public function setPosition(string $position): void
    {
        $this->position = $position;
    }
}
