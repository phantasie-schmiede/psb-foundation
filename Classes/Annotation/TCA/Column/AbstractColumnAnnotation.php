<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Annotation\TCA\Column;

use PSB\PsbFoundation\Annotation\TCA\AbstractTcaAnnotation;
use PSB\PsbFoundation\Utility\Configuration\TcaUtility;
use ReflectionException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use function in_array;

/**
 * Class AbstractColumnAnnotation
 *
 * @package PSB\PsbFoundation\Annotation\TCA\Column
 */
abstract class AbstractColumnAnnotation extends AbstractTcaAnnotation implements TcaAnnotationInterface
{
    public const EXCLUDED_FIELDS = [
        'palette',
        'position',
        'typeList',
    ];

    public const FIRST_LEVEL_CONFIGURATION_KEYS = [
        'description',
        'displayCond',
        'exclude',
        'l10n_display',
        'l10n_mode',
        'label',
        'onChange',
    ];

    public const POSITIONS = [
        'AFTER'   => 'after',
        'BEFORE'  => 'before',
        'PALETTE' => 'palette',
        'REPLACE' => 'replace',
        'TAB'     => 'tab',
    ];

    // Override this constant in extending classes!
    public const TYPE = '';

    public const TYPES = [
        'CATEGORY'    => 'category',
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
    protected mixed $default = null;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Columns/Properties/Description.html#example
     */
    protected ?string $description = null;

    /**
     * @var array|string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Columns/Properties/DisplayCond.html
     */
    protected string|array|null $displayCond = null;

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
     * You can use the keys 'after', 'before', 'palette', 'replace' and 'tab'.
     * If the referenced field belongs to a palette, there are also the options 'newLineAfter' and 'newLineBefore',
     * which will create a line break between this field and the referenced one.
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
     * @var string
     */
    protected string $typeList = '';

    /**
     * @return array|null
     */
    public function getBehaviour(): ?array
    {
        return $this->behaviour;
    }

    /**
     * @return mixed
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return array|string|null
     */
    public function getDisplayCond(): array|string|null
    {
        return $this->displayCond;
    }

    /**
     * @return string|null
     */
    public function getL10nDisplay(): ?string
    {
        return $this->l10nDisplay;
    }

    /**
     * @return string|null
     */
    public function getL10nMode(): ?string
    {
        return $this->l10nMode;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return string|null
     */
    public function getOnChange(): ?string
    {
        return $this->onChange;
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
        if (false === mb_strpos($location, '-')) {
            $location = $this->tcaService->convertPropertyNameToColumnName($location);
        }

        return $key . ':' . $location;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return static::TYPE;
    }

    /**
     * @return string
     */
    public function getTypeList(): string
    {
        return $this->typeList;
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

    /**
     * @param bool|null $allowLanguageSynchronization
     *
     * @return void
     */
    public function setAllowLanguageSynchronization(?bool $allowLanguageSynchronization): void
    {
        $this->behaviour['allowLanguageSynchronization'] = $allowLanguageSynchronization;
    }

    /**
     * @param array|null $behaviour
     *
     * @return void
     */
    public function setBehaviour(?array $behaviour): void
    {
        $this->behaviour = $behaviour;
    }

    /**
     * @param $default
     *
     * @return void
     */
    public function setDefault($default): void
    {
        $this->default = $default;
    }

    /**
     * @param string|null $description
     *
     * @return void
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @param array|string|null $displayCond
     *
     * @return void
     */
    public function setDisplayCond(array|string|null $displayCond): void
    {
        $this->displayCond = $displayCond;
    }

    /**
     * @param bool $exclude
     *
     * @return void
     */
    public function setExclude(bool $exclude): void
    {
        $this->exclude = $exclude;
    }

    /**
     * @param string|null $l10nDisplay
     *
     * @return void
     */
    public function setL10nDisplay(?string $l10nDisplay): void
    {
        $this->l10nDisplay = $l10nDisplay;
    }

    /**
     * @param string|null $l10nMode
     *
     * @return void
     */
    public function setL10nMode(?string $l10nMode): void
    {
        $this->l10nMode = $l10nMode;
    }

    /**
     * @param string $label
     *
     * @return void
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @param string|null $onChange
     *
     * @return void
     */
    public function setOnChange(?string $onChange): void
    {
        $this->onChange = $onChange;
    }

    /**
     * @param string $position
     *
     * @return void
     */
    public function setPosition(string $position): void
    {
        $this->position = $position;
    }

    /**
     * @param bool $readOnly
     *
     * @return void
     */
    public function setReadOnly(bool $readOnly): void
    {
        $this->readOnly = $readOnly;
    }

    /**
     * @param string $typeList
     *
     * @return void
     */
    public function setTypeList(string $typeList): void
    {
        $this->typeList = $typeList;
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

            if (in_array($key, self::FIRST_LEVEL_CONFIGURATION_KEYS, true)) {
                $fieldConfiguration[$key] = $value;
            } elseif (!in_array($key, self::EXCLUDED_FIELDS, true)) {
                $fieldConfiguration['config'][$key] = $value;
            }
        }

        return $fieldConfiguration;
    }
}
