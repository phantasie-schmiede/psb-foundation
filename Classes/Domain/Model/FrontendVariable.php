<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Domain\Model;

use JsonException;
use PSB\PsbFoundation\Attribute\TCA\ColumnType\Input;
use PSB\PsbFoundation\Attribute\TCA\Ctrl;
use PSB\PsbFoundation\Utility\StringUtility;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class FrontendVariable
 *
 * @package PSB\PsbFoundation\Domain\Model
 */
#[Ctrl(ignorePageTypeRestriction: true, label: 'name', rootLevel: -1)]
class FrontendVariable extends AbstractEntity
{
    #[Input]
    protected string $name = '';

    #[Input]
    protected string $value = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function getRawValue(): string
    {
        return $this->value;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function getValue(): mixed
    {
        return StringUtility::convertString($this->value);
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}
