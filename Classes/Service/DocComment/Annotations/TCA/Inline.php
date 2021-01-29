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

namespace PSB\PsbFoundation\Service\DocComment\Annotations\TCA;

use PSB\PsbFoundation\Exceptions\AnnotationException;
use PSB\PsbFoundation\Service\Configuration\Fields;
use PSB\PsbFoundation\Utility\ExtensionInformationUtility;
use ReflectionException;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException;

/**
 * Class Inline
 *
 * @Annotation
 * @package PSB\PsbFoundation\Service\DocComment\Annotations\TCA
 */
class Inline extends AbstractTcaFieldAnnotation
{
    public const TYPE = Fields::FIELD_TYPES['INLINE'];

    /**
     * @var array
     */
    protected array $appearance = [
        'collapseAll'                     => true,
        'enabledControls'                 => [
            'dragdrop' => true,
        ],
        'expandSingle'                    => true,
        'levelLinksPosition'              => 'bottom',
        'showAllLocalizationLink'         => true,
        'showPossibleLocalizationRecords' => true,
        'showRemovedLocalizationRecords'  => true,
        'showSynchronizationLink'         => true,
        'useSortable'                     => true,
    ];

    /**
     * @var string
     */
    protected string $foreignField = '';

    /**
     * @var string|null
     */
    protected ?string $foreignSortBy = null;

    /**
     * @var string
     */
    protected string $foreignTable = '';

    /**
     * Instead of directly specifying a foreign table, it is possible to specify a domain model class.
     *
     * @var string|null
     */
    protected ?string $linkedModel = null;

    /**
     * @var int
     */
    protected int $maxItems = 99;

    /**
     * @return array
     */
    public function getAppearance(): array
    {
        return $this->appearance;
    }

    /**
     * @param array $appearance
     */
    public function setAppearance(array $appearance): void
    {
        $this->appearance = $appearance;
    }

    /**
     * @return string
     * @throws AnnotationException
     * @throws Exception
     * @throws InvalidArgumentForHashGenerationException
     * @throws ReflectionException
     */
    public function getForeignField(): string
    {
        return ExtensionInformationUtility::convertPropertyNameToColumnName($this->foreignField);
    }

    /**
     * @param string $foreignField
     */
    public function setForeignField(string $foreignField): void
    {
        $this->foreignField = $foreignField;
    }

    /**
     * @return string|null
     */
    public function getForeignSortBy(): ?string
    {
        return $this->foreignSortBy;
    }

    /**
     * @param string|null $foreignSortBy
     */
    public function setForeignSortBy(?string $foreignSortBy): void
    {
        $this->foreignSortBy = $foreignSortBy;
    }

    /**
     * @return string
     */
    public function getForeignTable(): string
    {
        return $this->foreignTable;
    }

    /**
     * @param string $foreignTable
     */
    public function setForeignTable(string $foreignTable): void
    {
        $this->foreignTable = $foreignTable;
    }

    /**
     * @return string|null
     */
    public function getLinkedModel(): ?string
    {
        return $this->linkedModel;
    }

    /**
     * @param string $linkedModel
     *
     * @throws Exception
     */
    public function setLinkedModel(string $linkedModel): void
    {
        $this->linkedModel = $linkedModel;

        if (class_exists($linkedModel)) {
            $this->setForeignTable(ExtensionInformationUtility::convertClassNameToTableName($linkedModel));
        }
    }

    /**
     * @return int
     */
    public function getMaxItems(): int
    {
        return $this->maxItems;
    }

    /**
     * @param int $maxItems
     */
    public function setMaxItems(int $maxItems): void
    {
        $this->maxItems = $maxItems;
    }
}
