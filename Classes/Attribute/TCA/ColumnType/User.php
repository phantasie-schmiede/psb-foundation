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
 * Class User
 *
 * @link    https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/User/Index.html#properties-rendertype-default
 * @package PSB\PsbFoundation\Attribute\TCA\ColumnType
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class User extends AbstractColumnType
{
    /**
     * @param array|null $parameters
     * @param string     $renderType
     */
    public function __construct(
        protected ?array $parameters = null,
        protected string $renderType = '',
    ) {
    }

    /**
     * Database definition has to be added in ext_tables.sql of your extension!
     */
    public function getDatabaseDefinition(): string
    {
        return '';
    }

    /**
     * @return array|null
     */
    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    /**
     * @return string
     */
    public function getRenderType(): string
    {
        return $this->renderType;
    }
}
