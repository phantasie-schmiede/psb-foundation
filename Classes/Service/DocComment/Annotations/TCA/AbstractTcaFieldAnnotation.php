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

use PSB\PsbFoundation\Service\Configuration\TcaService;
use PSB\PsbFoundation\Service\DocComment\Annotations\AbstractAnnotation;
use PSB\PsbFoundation\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractTcaFieldAnnotation
 *
 * @package PSB\PsbFoundation\Service\DocComment\Annotations\TCA
 */
class AbstractTcaFieldAnnotation extends AbstractAnnotation implements TcaAnnotationInterface
{
    public const  TYPE = '';

    /**
     * @var array|string|null
     */
    protected $displayCond;

    /**
     * If set to true, \PSB\PsbFoundation\ViewHelpers\Form\BuildFromTcaViewHelper can be used for this domain model.
     * This accounts only for this property. In order to activate this feature for all properties of this model, see
     * Ctrl annotation.
     *
     * @var bool
     * @see Ctrl
     */
    protected bool $editableInFrontend = false;

    /**
     * @var bool
     */
    protected bool $exclude = false;

    /**
     * @var string|null
     */
    protected ?string $label = null;

    /**
     * @return array|string|null
     */
    public function getDisplayCond()
    {
        return $this->displayCond;
    }

    /**
     * @param array|string|null $displayCond
     */
    public function setDisplayCond($displayCond): void
    {
        $this->displayCond = $displayCond;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string|null $label
     */
    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return static::TYPE;
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
    public function isExclude(): bool
    {
        return $this->exclude;
    }

    /**
     * @param bool $exclude
     */
    public function setExclude(bool $exclude): void
    {
        $this->exclude = $exclude;
    }

    /**
     * @param array $properties
     *
     * @return array
     */
    public static function propertyPreProcessor(array $properties): array
    {
        if (isset($properties['linkedModel']) && !StringUtility::endsWith($properties['linkedModel'], '::class')) {
            $properties['linkedModel'] .= '::class';
        }

        return $properties;
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
        $fieldConfiguration = [];
        $fieldConfiguration['config']['type'] = $this->getType();

        foreach ($properties as $key => $value) {
            $key = TcaService::convertKey($key);

            if (in_array($key, ['displayCond', 'exclude', 'label'], true)) {
                $fieldConfiguration[$key] = $value;
            } else {
                $fieldConfiguration['config'][$key] = $value;
            }
        }

        return $fieldConfiguration;
    }
}
