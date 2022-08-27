<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Annotation\TCA\Column;

/**
 * Class Slug
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA\Column
 */
class Slug extends AbstractColumnAnnotation
{
    public const TYPE = self::TYPES['SLUG'];

    /**
     * @var string
     */
    protected mixed $default = '';

    /**
     * @var string
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Slug/Properties/Eval.html
     */
    protected string $eval = 'uniqueInSite';

    /**
     * @var string
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Slug/Properties/FallbackCharacter.html
     */
    protected string $fallbackCharacter = '-';

    /**
     * @var array
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Slug/Properties/GeneratorOptions.html
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
    public function getEval(): string
    {
        return $this->eval;
    }

    /**
     * @return string
     */
    public function getFallbackCharacter(): string
    {
        return $this->fallbackCharacter;
    }

    /**
     * @return array
     */
    public function getGeneratorOptions(): array
    {
        return $this->generatorOptions;
    }

    /**
     * @param string $eval
     *
     * @return void
     */
    public function setEval(string $eval): void
    {
        $this->eval = $eval;
    }

    /**
     * @param string $fallbackCharacter
     *
     * @return void
     */
    public function setFallbackCharacter(string $fallbackCharacter): void
    {
        $this->fallbackCharacter = $fallbackCharacter;
    }

    /**
     * @param array $generatorOptions
     *
     * @return void
     */
    public function setGeneratorOptions(array $generatorOptions): void
    {
        $this->generatorOptions = $generatorOptions;
    }
}
