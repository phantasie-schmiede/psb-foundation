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
use PSB\PsbFoundation\Service\Configuration\TcaService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Group
 *
 * @package PSB\PsbFoundation\Attribute\TCA\ColumnType
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Group extends AbstractColumnType
{
    /**
     * @var TcaService
     */
    protected TcaService $tcaService;

    /**
     * @param string|null $allowed                   https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Group/Properties/Allowed.html
     * @param array|null  $elementBrowserEntryPoints https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Group/Properties/ElementBrowserEntryPoints.html
     * @param string|null $foreignTable              https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Group/Properties/ForeignTable.html
     * @param string      $linkedModel               Instead of directly specifying a foreign table, it is possible to
     *                                               specify a domain model class.
     * @param int|null    $maxItems                  https://docs.typo3.org/m/typo3/reference-tca/12.4/en-us/ColumnsConfig/CommonProperties/Maxitems.html
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function __construct(
        protected ?string $allowed = null,
        protected ?array  $elementBrowserEntryPoints = null,
        protected ?string $foreignTable = null,
        protected string  $linkedModel = '',
        protected ?int    $maxItems = null,
    ) {
        $this->tcaService = GeneralUtility::makeInstance(TcaService::class);

        if (class_exists($linkedModel)) {
            $this->foreignTable = $this->tcaService->convertClassNameToTableName($linkedModel);
        }
    }

    /**
     * @return string|null
     */
    public function getAllowed(): ?string
    {
        return $this->allowed;
    }

    /**
     * @return string
     */
    public function getDatabaseDefinition(): string
    {
        if (empty($this->mm)) {
            return self::DATABASE_DEFINITIONS['TEXT'];
        }

        return self::DATABASE_DEFINITIONS['INTEGER_UNSIGNED'];
    }

    /**
     * @return array|null
     */
    public function getElementBrowserEntryPoints(): ?array
    {
        return $this->elementBrowserEntryPoints;
    }

    /**
     * @return string|null
     */
    public function getForeignTable(): ?string
    {
        return $this->foreignTable;
    }

    /**
     * @return int|null
     */
    public function getMaxItems(): ?int
    {
        return $this->maxItems;
    }
}
