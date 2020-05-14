<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Service\DocComment\Annotations\TCA;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use PSB\PsbFoundation\Exceptions\AnnotationException;
use PSB\PsbFoundation\Service\Configuration\TcaService;
use PSB\PsbFoundation\Service\DocComment\Annotations\AbstractAnnotation;
use PSB\PsbFoundation\Utility\ExtensionInformationUtility;
use ReflectionException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException;

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
     * @throws AnnotationException
     * @throws Exception
     * @throws InvalidArgumentForHashGenerationException
     * @throws ReflectionException
     */
    public function getLabel(): string
    {
        return ExtensionInformationUtility::convertPropertyNameToColumnName($this->label);
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
        if (null === $this->labelAlt) {
            return null;
        }

        $altLabels = GeneralUtility::trimExplode(',', $this->labelAlt);

        array_walk($altLabels, static function (&$item) {
            $item = ExtensionInformationUtility::convertPropertyNameToColumnName($item);
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

    /**
     * @param string $targetName
     * @param string $targetScope
     *
     * @return array
     */
    public function toArray(string $targetName, string $targetScope): array
    {
        $properties = parent::toArray($targetName, $targetScope);
        $ctrlConfiguration = [];

        foreach ($properties as $key => $value) {
            $ctrlConfiguration[TcaService::convertKey($key)] = $value;
        }

        return $ctrlConfiguration;
    }
}
