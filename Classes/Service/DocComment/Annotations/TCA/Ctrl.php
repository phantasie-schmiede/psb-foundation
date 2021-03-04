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

use Exception;
use PSB\PsbFoundation\Service\Configuration\TcaService;
use PSB\PsbFoundation\Service\DocComment\Annotations\AbstractAnnotation;
use PSB\PsbFoundation\Utility\Configuration\TcaUtility;
use ReflectionException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class TcaConfig
 *
 * https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html
 *
 * @Annotation
 * @package PSB\PsbFoundation\Service\DocComment\Annotations\TCA
 */
class Ctrl extends AbstractAnnotation
{
    /**
     * @var string|null
     */
    protected ?string $defaultSortBy = 'uid DESC';

    /**
     * If set to true, \PSB\PsbFoundation\ViewHelpers\Form\BuildFromTcaViewHelper can be used for this domain model.
     * This accounts for all properties annotated with \PSB\PsbFoundation\Service\DocComment\Annotations\TCA\*. In
     * order to activate this feature only for certain properties, see AbstractTcaFieldAnnotation.
     *
     * @var bool
     * @see AbstractTcaFieldAnnotation
     */
    protected bool $editableInFrontend = false;

    /**
     * @var bool
     */
    protected bool $hideTable = false;

    /**
     * @var string
     */
    protected string $label = '';

    /**
     * @var string|null
     */
    protected ?string $labelAlt = null;

    /**
     * @var bool
     */
    protected bool $labelAltForce = false;

    /**
     * @var int
     */
    protected int $rootLevel = 0;

    /**
     * @var string|null
     */
    protected ?string $searchFields = null;

    /**
     * @var string|null
     */
    protected ?string $sortBy = null;

    /**
     * @var TcaService
     */
    protected TcaService $tcaService;

    /**
     * AbstractTcaFieldAnnotation constructor.
     *
     * @param array $data
     *
     * @throws Exception
     */
    public function __construct(array $data = [])
    {
        $this->tcaService = GeneralUtility::makeInstance(TcaService::class);
        parent::__construct($data);
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function toArray(): array
    {
        $properties = parent::toArray();
        $ctrlConfiguration = [];

        foreach ($properties as $key => $value) {
            $ctrlConfiguration[TcaUtility::convertKey($key)] = $value;
        }

        return $ctrlConfiguration;
    }

    /**
     * @return string|null
     */
    public function getDefaultSortBy(): ?string
    {
        return $this->defaultSortBy;
    }

    /**
     * @param string|null $defaultSortBy
     */
    public function setDefaultSortBy(?string $defaultSortBy): void
    {
        $this->defaultSortBy = $defaultSortBy;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        if (null === $this->tcaService) {
            return $this->label;
        }

        return $this->tcaService->convertPropertyNameToColumnName($this->label);
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @return string|null
     */
    public function getLabelAlt(): ?string
    {
        if (null === $this->labelAlt || null === $this->tcaService) {
            return null;
        }

        $altLabels = GeneralUtility::trimExplode(',', $this->labelAlt);

        array_walk($altLabels, function (&$item) {
            $item = $this->tcaService->convertPropertyNameToColumnName($item);
        });

        return implode(',', $altLabels);
    }

    /**
     * @param string|null $labelAlt
     */
    public function setLabelAlt(?string $labelAlt): void
    {
        $this->labelAlt = $labelAlt;
    }

    /**
     * @return int
     */
    public function getRootLevel(): int
    {
        return $this->rootLevel;
    }

    /**
     * @param int $rootLevel
     */
    public function setRootLevel(int $rootLevel): void
    {
        $this->rootLevel = $rootLevel;
    }

    /**
     * @return string|null
     */
    public function getSearchFields(): ?string
    {
        return $this->searchFields;
    }

    /**
     * @param string|null $searchFields
     */
    public function setSearchFields(?string $searchFields): void
    {
        $this->searchFields = $searchFields;
    }

    /**
     * @return string|null
     */
    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    /**
     * @param string|null $sortBy
     */
    public function setSortBy(?string $sortBy): void
    {
        $this->setDefaultSortBy(null);
        $this->sortBy = $sortBy;
    }

    /**
     * @return bool
     */
    public function isEditableInFrontend(): bool
    {
        return $this->editableInFrontend;
    }

    /**
     * @param bool $editableInFrontend
     */
    public function setEditableInFrontend(bool $editableInFrontend): void
    {
        $this->editableInFrontend = $editableInFrontend;
    }

    /**
     * @return bool
     */
    public function isHideTable(): bool
    {
        return $this->hideTable;
    }

    /**
     * @param bool $hideTable
     */
    public function setHideTable(bool $hideTable): void
    {
        $this->hideTable = $hideTable;
    }

    /**
     * @return bool
     */
    public function isLabelAltForce(): bool
    {
        return $this->labelAltForce;
    }

    /**
     * @param bool $labelAltForce
     */
    public function setLabelAltForce(bool $labelAltForce): void
    {
        $this->labelAltForce = $labelAltForce;
    }
}
