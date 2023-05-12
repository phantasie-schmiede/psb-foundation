<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Attribute\TCA\ColumnType;

use Attribute;

/**
 * Class Slug
 *
 * @package PSB\PsbFoundation\Attribute\TCA\ColumnType
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Slug extends AbstractColumnType
{
    // Database field for type slug is added by TYPO3 automatically.
    public const DATABASE_DEFINITION = '';

    /**
     * @param mixed  $default
     * @param string $eval              https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Slug/Properties/Eval.html
     * @param string $fallbackCharacter https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Slug/Properties/FallbackCharacter.html
     * @param array  $generatorOptions  https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Slug/Properties/GeneratorOptions.html
     */
    public function __construct(
        protected string $default = '',
        protected string $eval = 'uniqueInSite',
        protected string $fallbackCharacter = '-',
        protected array $generatorOptions = [
            'fields'               => ['title', 'nav_title'],
            'fieldSeparator'       => '/',
            'prefixParentPageSlug' => true,
            'replacements'         => [
                '/' => '',
            ],
        ],
    ) {
    }

    /**
     * @return string
     */
    public function getDefault(): string
    {
        return $this->default;
    }

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
}
