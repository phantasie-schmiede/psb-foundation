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

namespace PSB\PsbFoundation\Annotation\TCA;

use Exception;
use PSB\PsbFoundation\Annotation\AbstractAnnotation;
use PSB\PsbFoundation\Service\Configuration\TcaService;
use PSB\PsbFoundation\Utility\Configuration\TcaUtility;
use ReflectionException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractTcaFieldAnnotation
 *
 * @package PSB\PsbFoundation\Annotation\TCA
 */
class AbstractTcaFieldAnnotation extends AbstractAnnotation implements TcaAnnotationInterface
{
    // Override this constant in extending classes!
    public const  TYPE = '';

    public const TYPE_LIST_NONE = 'none';

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
    protected ?string $l10nDisplay = null;

    /**
     * @var string|null
     */
    protected ?string $l10nMode = null;

    /**
     * @var string
     */
    protected string $label = '';

    /**
     * @var string|null
     */
    protected ?string $onChange = null;

    /**
     * @var string
     */
    protected string $position = '';

    /**
     * @var bool
     */
    protected bool $readOnly = false;

    /**
     * @var TcaService
     */
    protected TcaService $tcaService;

    /**
     * @var string|null
     */
    protected ?string $typeList = null;

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
        $fieldConfiguration = [];
        $fieldConfiguration['config']['type'] = $this->getType();

        foreach ($properties as $key => $value) {
            $key = TcaUtility::convertKey($key);

            if (in_array($key, ['displayCond', 'exclude', 'label', 'l10n_display', 'l10n_mode', 'onChange'], true)) {
                $fieldConfiguration[$key] = $value;
            } elseif ('position' !== $key) {
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
    public function getL10nDisplay(): ?string
    {
        return $this->l10nDisplay;
    }

    /**
     * @param string|null $l10nDisplay
     */
    public function setL10nDisplay(?string $l10nDisplay): void
    {
        $this->l10nDisplay = $l10nDisplay;
    }

    /**
     * @return string|null
     */
    public function getL10nMode(): ?string
    {
        return $this->l10nMode;
    }

    /**
     * @param string|null $l10nMode
     */
    public function setL10nMode(?string $l10nMode): void
    {
        $this->l10nMode = $l10nMode;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
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
     * @return string
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * @param string $position
     */
    public function setPosition(string $position): void
    {
        $this->position = $position;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return static::TYPE;
    }

    /**
     * @return string|null
     */
    public function getTypeList(): ?string
    {
        return $this->typeList;
    }

    /**
     * @param string|null $typeList
     */
    public function setTypeList(?string $typeList): void
    {
        $this->typeList = $typeList;
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

    /**
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    /**
     * @param bool $readOnly
     */
    public function setReadOnly(bool $readOnly): void
    {
        $this->readOnly = $readOnly;
    }
}
