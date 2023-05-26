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
use PSB\PsbFoundation\Enum\Relationship;
use PSB\PsbFoundation\Service\Configuration\TcaService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Category
 *
 * @package PSB\PsbFoundation\Attribute\TCA\ColumnType
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Category extends AbstractColumnType
{
    public const DATABASE_DEFINITION = 'int(11) unsigned DEFAULT \'0\'';

    /**
     * @var TcaService
     */
    protected TcaService $tcaService;

    /**
     * @param array        $exclusiveKeys           https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Category/Properties/ExclusiveKeys.html|null
     * @param Relationship $relationship            https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Category/Properties/Relationship.html
     * @param array|null   $treeConfig              https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Category/Properties/TreeConfig.html
     * @param string|null  $treeConfigChildrenField You can use the property name. It will be converted to the column
     *                                              name automatically.
     * @param string|null  $treeConfigDataProvider
     * @param bool|null    $treeConfigExpandAll
     * @param int|null     $treeConfigMaxLevels
     * @param string|null  $treeConfigNonSelectableLevels
     * @param string|null  $treeConfigParentField   You can use the property name. It will be converted to the column
     *                                              name automatically.
     * @param bool|null    $treeConfigShowHeader
     * @param array        $treeConfigStartingPoints
     */
    public function __construct(
        protected array        $exclusiveKeys = [],
        protected Relationship $relationship = Relationship::manyToMany,
        protected ?array       $treeConfig = null,
        protected ?string      $treeConfigChildrenField = null,
        protected ?string      $treeConfigDataProvider = null,
        protected ?bool        $treeConfigExpandAll = null,
        protected ?int         $treeConfigMaxLevels = null,
        protected ?string      $treeConfigNonSelectableLevels = null,
        protected ?string      $treeConfigParentField = null,
        protected ?bool        $treeConfigShowHeader = null,
        protected array        $treeConfigStartingPoints = [],
    ) {
        $this->tcaService = GeneralUtility::makeInstance(TcaService::class);
    }

    /**
     * @return string|null
     */
    public function getExclusiveKeys(): ?string
    {
        return $this->exclusiveKeys ? implode(', ', $this->exclusiveKeys) : null;
    }

    /**
     * @return string
     */
    public function getRelationship(): string
    {
        return $this->relationship->value;
    }

    /**
     * @return array|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function getTreeConfig(): ?array
    {
        if (null !== $this->treeConfigExpandAll) {
            $configuration['appearance']['expandAll'] = $this->treeConfigExpandAll;
        }

        if (0 < $this->treeConfigMaxLevels) {
            $configuration['appearance']['maxLevels'] = $this->treeConfigMaxLevels;
        }

        if (null !== $this->treeConfigNonSelectableLevels) {
            $configuration['appearance']['nonSelectableLevels'] = $this->treeConfigNonSelectableLevels;
        }

        if (null !== $this->treeConfigShowHeader) {
            $configuration['appearance']['showHeader'] = $this->treeConfigShowHeader;
        }

        if (null !== $this->treeConfigChildrenField) {
            $configuration['childrenField'] = $this->tcaService->convertPropertyNameToColumnName($this->treeConfigChildrenField);
        }

        if (null !== $this->treeConfigDataProvider) {
            $configuration['dataProvider'] = $this->treeConfigDataProvider;
        }

        if (null !== $this->treeConfigParentField) {
            $configuration['parentField'] = $this->tcaService->convertPropertyNameToColumnName($this->treeConfigParentField);
        }

        if (!empty($this->treeConfigStartingPoints)) {
            $configuration['startingPoints'] = implode(', ', $this->treeConfigStartingPoints);
        }

        return $configuration ?? null;
    }
}
