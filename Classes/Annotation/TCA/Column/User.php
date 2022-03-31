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
 * Class User
 *
 * @Annotation
 * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/User/Index.html#properties-rendertype-default
 * @package PSB\PsbFoundation\Annotation\TCA\Column
 */
class User extends AbstractColumnAnnotation
{
    public const TYPE = self::TYPES['USER'];

    /**
     * @var array
     */
    protected array $parameters = [];

    /**
     * @var string
     */
    protected string $renderType = '';

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getRenderType(): string
    {
        return $this->renderType;
    }

    /**
     * @param string $renderType
     */
    public function setRenderType(string $renderType): void
    {
        $this->renderType = $renderType;
    }
}
