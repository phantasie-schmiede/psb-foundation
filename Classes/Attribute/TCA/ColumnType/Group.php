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
     * @param string|null $linkedModel               Instead of directly specifying a foreign table, it is possible to
     *                                               specify a domain model class.
     */
    public function __construct(
        protected ?string $allowed = null,
        protected ?array  $elementBrowserEntryPoints = null,
        protected ?string $foreignTable = null,
        protected ?string $linkedModel = null,
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
}
