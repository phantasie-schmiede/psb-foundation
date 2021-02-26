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
use PSB\PsbFoundation\Utility\StringUtility;
use ReflectionException;
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
    protected ?bool $editableInFrontend = null;

    /**
     * @var bool
     */
    protected bool $exclude = false;

    /**
     * @var string|null
     */
    protected ?string $label = null;

    /**
     * @var string|null
     */
    protected ?string $onChange = null;

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
     * @param string      $className
     * @param string|null $methodOrPropertyName
     * @param array       $namespaces
     * @param array       $properties
     *
     * @return array
     */
    public static function propertyPreProcessor(
        string $className,
        ?string $methodOrPropertyName,
        array $namespaces,
        array $properties
    ): array {
        if (isset($properties['linkedModel']) && !StringUtility::endsWith($properties['linkedModel'], '::class')) {
            $properties['linkedModel'] .= '::class';
        }

        return $properties;
    }

    /**
     * @param string $targetScope
     *
     * @return array
     * @throws ReflectionException
     */
    public function toArray(string $targetScope): array
    {
        $properties = parent::toArray($targetScope);
        $fieldConfiguration = [];
        $fieldConfiguration['config']['type'] = $this->getType();

        foreach ($properties as $key => $value) {
            $key = TcaUtility::convertKey($key);

            if (in_array($key, ['displayCond', 'exclude', 'label', 'onChange'], true)) {
                $fieldConfiguration[$key] = $value;
            } else {
                $fieldConfiguration['config'][$key] = $value;
            }
        }

        return $fieldConfiguration;
    }

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
     * @return string|null
     */
    public function getOnChange(): ?string
    {
        return $this->onChange;
    }

    /**
     * @param string|null $onChange
     */
    public function setOnChange(?string $onChange): void
    {
        $this->onChange = $onChange;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return static::TYPE;
    }

    /**
     * @return bool|null
     */
    public function isEditableInFrontend(): ?bool
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
}
