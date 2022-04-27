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
 * Class Palette
 *
 * @Annotation
 * @link    https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Palettes/Index.html
 * @package PSB\PsbFoundation\Annotation\TCA
 */
class Palette extends AbstractTcaAnnotation
{
    public const SPECIAL_FIELDS = [
        'LINE_BREAK' => '--linebreak--',
    ];

    // These values can be used by annotations of type AbstractColumnAnnotation, if placed inside a palette.
    public const SPECIAL_POSITIONS = [
        'NEW_LINE_AFTER'  => 'newLineAfter',
        'NEW_LINE_BEFORE' => 'newLineBefore',
    ];

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Palettes/Properties/Description.html
     */
    protected string $description = '';

    /**
     * @var string
     */
    protected string $identifier = '';

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Palettes/Properties/Label.html
     */
    protected string $label = '';

    /**
     * Usage: 'key:propertyName'
     * You can use the keys 'after', 'before', 'replace' and 'tab'.
     *
     * @var string
     */
    protected string $position = '';

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
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
        if (false === mb_strpos($location, '-')) {
            $location = $this->tcaService->convertPropertyNameToColumnName($location);
        }

        return $key . ':' . $location;
    }

    /**
     * @param string|null $description
     *
     * @return void
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
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
