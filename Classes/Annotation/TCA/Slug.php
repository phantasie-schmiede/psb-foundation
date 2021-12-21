<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace PSB\PsbFoundation\Annotation\TCA;

use PSB\PsbFoundation\Library\TcaFields;

/**
 * Class Slug
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA
 */
class Slug extends AbstractTcaFieldAnnotation
{
    public const TYPE = TcaFields::TYPES['SLUG'];

    /**
     * @var string
     */
    protected string $default = '';

    /**
     * @var string
     */
    protected string $eval = 'uniqueInSite';

    /**
     * @var string
     */
    protected string $fallbackCharacter = '-';

    /**
     * @var array
     */
    protected array $generatorOptions = [
        'fields'               => ['title', 'nav_title'],
        'fieldSeparator'       => '/',
        'prefixParentPageSlug' => true,
        'replacements'         => [
            '/' => '',
        ],
    ];

    /**
     * @return string
     */
    public function getDefault(): string
    {
        return $this->default;
    }

    /**
     * @param string $default
     */
    public function setDefault(string $default): void
    {
        $this->default = $default;
    }

    /**
     * @return string
     */
    public function getEval(): string
    {
        return $this->eval;
    }

    /**
     * @param string $eval
     */
    public function setEval(string $eval): void
    {
        $this->eval = $eval;
    }

    /**
     * @return string
     */
    public function getFallbackCharacter(): string
    {
        return $this->fallbackCharacter;
    }

    /**
     * @param string $fallbackCharacter
     */
    public function setFallbackCharacter(string $fallbackCharacter): void
    {
        $this->fallbackCharacter = $fallbackCharacter;
    }

    /**
     * @return array
     */
    public function getGeneratorOptions(): array
    {
        return $this->generatorOptions;
    }

    /**
     * @param array $generatorOptions
     */
    public function setGeneratorOptions(array $generatorOptions): void
    {
        $this->generatorOptions = $generatorOptions;
    }
}
