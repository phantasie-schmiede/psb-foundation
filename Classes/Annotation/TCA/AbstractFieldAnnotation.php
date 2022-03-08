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
 * Class AbstractFieldAnnotation
 *
 * @package PSB\PsbFoundation\Annotation\TCA
 */
abstract class AbstractFieldAnnotation extends AbstractAnnotation implements TcaAnnotationInterface
{
    // Override this constant in extending classes!
    public const  TYPE = '';

    public const TYPES = [
        'CHECKBOX'    => 'check',
        'DOCUMENT'    => 'document',
        'FILE'        => 'file',
        'GROUP'       => 'group',
        'IMAGE'       => 'image',
        'INLINE'      => 'inline',
        'INPUT'       => 'input',
        'PASSTHROUGH' => 'passthrough',
        'SELECT'      => 'select',
        'SLUG'        => 'slug',
        'TEXT'        => 'text',
        'USER'        => 'user',
    ];

    // If you don't want a field to be shown in backend at all, set this value for typeList.
    public const TYPE_LIST_NONE = 'none';

    /**
     * @var bool|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/CommonProperties/BehaviourAllowLanguageSynchronization.html
     */
    protected ?bool $allowLanguageSynchronization = null;

    /**
     * @var array|null
     */
    protected ?array $behaviour = null;

    /**
     * @var mixed
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/CommonProperties/Default.html
     */
    protected $default;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Columns/Properties/Description.html#example
     */
    protected ?string $description = null;

    /**
     * @var array|string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Columns/Properties/DisplayCond.html
     */
    protected $displayCond;

    /**
     * @var bool|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Columns/Properties/Exclude.html
     */
    protected ?bool $exclude = null;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Columns/Properties/L10nDisplay.html
     */
    protected ?string $l10nDisplay = null;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Columns/Properties/L10nMode.html
     */
    protected ?string $l10nMode = null;

    /**
     * @var string
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Columns/Properties/Label.html
     */
    protected string $label = '';

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Columns/Properties/OnChange.html
     */
    protected ?string $onChange = null;

    /**
     * Usage: 'key:propertyName'
     * You can use the keys 'after', 'before' and 'replace'.
     *
     * @var string
     */
    protected string $position = '';

    /**
     * @var bool|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/CommonProperties/ReadOnly.html
     */
    protected ?bool $readOnly = null;

    /**
     * @var TcaService
     */
    protected TcaService $tcaService;

    /**
     * @var string|null
     */
    protected ?string $typeList = null;

    /**
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
     * @param bool|null $allowLanguageSynchronization
     */
    public function setAllowLanguageSynchronization(?bool $allowLanguageSynchronization): void
    {
        $this->behaviour['allowLanguageSynchronization'] = $allowLanguageSynchronization;
    }

    /**
     * @param bool $exclude
     */
    public function setExclude(bool $exclude): void
    {
        $this->exclude = $exclude;
    }

    /**
     * @param bool $readOnly
     */
    public function setReadOnly(bool $readOnly): void
    {
        $this->readOnly = $readOnly;
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

            if (in_array($key, [
                'description',
                'displayCond',
                'exclude',
                'l10n_display',
                'l10n_mode',
                'label',
                'onChange',
            ], true)) {
                $fieldConfiguration[$key] = $value;
            } elseif ('position' !== $key) {
                $fieldConfiguration['config'][$key] = $value;
            }
        }

        return $fieldConfiguration;
    }

    /**
     * @return array|null
     */
    public function getBehaviour(): ?array
    {
        return $this->behaviour;
    }

    /**
     * @param array|null $behaviour
     */
    public function setBehaviour(?array $behaviour): void
    {
        $this->behaviour = $behaviour;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     */
    public function setDefault($default): void
    {
        $this->default = $default;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
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
        if (empty($this->position)) {
            return '';
        }

        [$key, $location] = GeneralUtility::trimExplode(':', $this->position, false, 2);

        // Check if $location is NOT a palette name.
        if (false !== mb_strpos($location, '-')) {
            $location = $this->tcaService->convertPropertyNameToColumnName($location);
        }

        return $key . ':' . $location;
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
    public function isExclude(): ?bool
    {
        return $this->exclude;
    }

    /**
     * @return bool|null
     */
    public function isReadOnly(): ?bool
    {
        return $this->readOnly;
    }
}
