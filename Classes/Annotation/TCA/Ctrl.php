<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Annotation\TCA;

use Exception;
use PSB\PsbFoundation\Service\Configuration\TcaService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class TcaConfig
 *
 * @Annotation
 * @link    https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Index.html
 * @package PSB\PsbFoundation\Annotation\TCA
 */
class Ctrl extends AbstractTcaAnnotation
{
    public const ENABLE_COLUMNS = [
        self::ENABLE_COLUMN_IDENTIFIERS['DISABLED']  => 'hidden',
        self::ENABLE_COLUMN_IDENTIFIERS['ENDTIME']   => 'endtime',
        self::ENABLE_COLUMN_IDENTIFIERS['STARTTIME'] => 'starttime',
    ];

    public const ENABLE_COLUMN_IDENTIFIERS = [
        'DISABLED'  => 'disabled',
        'ENDTIME'   => 'endtime',
        'STARTTIME' => 'starttime',
    ];
    /**
     * @var array|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Ext.html
     */
    protected ?array $EXT = null;

    /**
     * This property holds the property names which have been defined explicitly as annotation arguments. This allows
     * to decide which values to use when overriding an existing configuration.
     *
     * @var string[]
     */
    protected array $_setProperties = [];

    /**
     * @var bool|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/AdminOnly.html
     */
    protected ?bool $adminOnly = null;

    /**
     * @var array|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Container.html
     */
    protected ?array $container = null;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/CopyAfterDuplFields.html
     */
    protected ?string $copyAfterDuplFields = null;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Crdate.html
     */
    protected ?string $crdate = 'crdate';

    /**
     * @var string
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/CruserId.html
     */
    protected string $cruser_id = 'cruser_id';

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/DefaultSortby.html
     */
    protected ?string $default_sortby = 'uid DESC';

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Delete.html
     */
    protected ?string $delete = 'deleted';

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/DescriptionColumn.html
     */
    protected ?string $descriptionColumn = null;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Editlock.html
     */
    protected ?string $editlock = null;

    /**
     * @var array|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Enablecolumns.html
     */
    protected ?array $enablecolumns = self::ENABLE_COLUMNS;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/FormattedLabelUserFunc.html
     */
    protected ?string $formattedLabel_userFunc = null;

    /**
     * @var array|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/FormattedLabelUserFuncOptions.html
     */
    protected ?array $formattedLabel_userFunc_options = null;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/GroupName.html
     */
    protected ?string $groupName = null;

    /**
     * @var bool|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/HideAtCopy.html
     */
    protected ?bool $hideAtCopy = null;

    /**
     * @var bool|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/HideTable.html
     */
    protected ?bool $hideTable = null;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Iconfile.html
     */
    protected ?string $iconfile = 'EXT:core/Resources/Public/Icons/T3Icons/svgs/mimetypes/mimetypes-x-sys_action.svg';

    /**
     * @var bool|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/IsStatic.html
     */
    protected ?bool $is_static = null;

    /**
     * You can use the property name. It will be converted to the column name automatically.
     *
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Label.html
     */
    protected ?string $label = 'uid';

    /**
     * You can use property names. They will be converted to their column names automatically.
     *
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Label.html
     */
    protected ?string $label_alt = null;

    /**
     * @var bool|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Label.html
     */
    protected ?bool $label_alt_force = null;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/LabelUserfunc.html
     */
    protected ?string $label_userFunc = null;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/LanguageField.html
     */
    protected ?string $languageField = 'sys_language_uid';

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/OrigUid.html
     */
    protected ?string $origUid = 't3_origuid';

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/PrependAtCopy.html
     */
    protected ?string $prependAtCopy = null;

    /**
     * @var bool|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/ReadOnly.html
     */
    protected ?bool $readOnly = null;

    /**
     * @var int|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/RootLevel.html
     */
    protected ?int $rootLevel = null;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/SearchFields.html
     */
    protected ?string $searchFields = null;

    /**
     * @var array|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Security.html
     */
    protected ?array $security = null;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/SeliconField.html
     */
    protected ?string $selicon_field = null;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/ShadowColumnsForNewPlaceholders.html
     */
    protected ?string $shadowColumnsForNewPlaceholders = null;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Sortby.html
     */
    protected ?string $sortby = null;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Title.html
     */
    protected ?string $title = null;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/TransOrigDiffSourceField.html
     */
    protected ?string $transOrigDiffSourceField = 'l10n_diffsource';

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/TransOrigPointerField.html
     */
    protected ?string $transOrigPointerField = 'l10n_parent';

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/TranslationSource.html
     */
    protected ?string $translationSource = 'l10n_source';

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Tstamp.html
     */
    protected ?string $tstamp = 'tstamp';

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Type.html
     */
    protected ?string $type = null;

    /**
     * @var array|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/TypeiconClasses.html
     */
    protected ?array $typeicon_classes = null;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/TypeiconColumn.html
     */
    protected ?string $typeicon_column = null;

    /**
     * @var string|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/UseColumnsForDefaultValues.html
     */
    protected ?string $useColumnsForDefaultValues = null;

    /**
     * @var bool|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/VersioningWS.html
     */
    protected ?bool $versioningWS = null;

    /**
     * @var bool|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/VersioningWSAlwaysAllowLiveEdit.html
     */
    protected ?bool $versioningWS_alwaysAllowLiveEdit = null;

    /**
     * @param array $data
     *
     * @throws Exception
     */
    public function __construct(array $data = [])
    {
        $this->_setProperties = array_keys($data);
        $this->tcaService = GeneralUtility::makeInstance(TcaService::class);
        parent::__construct($data);
    }

    /**
     * Function name doesn't follow convention to exclude it from array conversion (see toArray())!
     *
     * @return string[]
     */
    public function _getSetProperties(): array
    {
        return $this->_setProperties;
    }

    /**
     * @return bool|null
     */
    public function getAdminOnly(): ?bool
    {
        return $this->adminOnly;
    }

    /**
     * @return array|null
     */
    public function getContainer(): ?array
    {
        return $this->container;
    }

    /**
     * @return string|null
     */
    public function getCopyAfterDuplFields(): ?string
    {
        return $this->copyAfterDuplFields;
    }

    /**
     * @return string|null
     */
    public function getCrdate(): ?string
    {
        return $this->crdate;
    }

    /**
     * @return string
     */
    public function getCruserId(): string
    {
        return $this->cruser_id;
    }

    /**
     * @return string|null
     */
    public function getDefaultSortBy(): ?string
    {
        return $this->default_sortby;
    }

    /**
     * @return string|null
     */
    public function getDelete(): ?string
    {
        return $this->delete;
    }

    /**
     * @return string|null
     */
    public function getDescriptionColumn(): ?string
    {
        return $this->descriptionColumn;
    }

    /**
     * @return array|null
     */
    public function getEXT(): ?array
    {
        return $this->EXT;
    }

    /**
     * @return string|null
     */
    public function getEditlock(): ?string
    {
        return $this->editlock;
    }

    /**
     * @return array|null
     */
    public function getEnablecolumns(): ?array
    {
        return $this->enablecolumns;
    }

    /**
     * @return string|null
     */
    public function getFormattedLabelUserFunc(): ?string
    {
        return $this->formattedLabel_userFunc;
    }

    /**
     * @return array|null
     */
    public function getFormattedLabelUserFuncOptions(): ?array
    {
        return $this->formattedLabel_userFunc_options;
    }

    /**
     * @return string|null
     */
    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    /**
     * @return bool|null
     */
    public function getHideAtCopy(): ?bool
    {
        return $this->hideAtCopy;
    }

    /**
     * @return bool|null
     */
    public function getHideTable(): ?bool
    {
        return $this->hideTable;
    }

    /**
     * @return string|null
     */
    public function getIconfile(): ?string
    {
        return $this->iconfile;
    }

    /**
     * @return bool|null
     */
    public function getIsStatic(): ?bool
    {
        return $this->is_static;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->tcaService->convertPropertyNameToColumnName($this->label);
    }

    /**
     * @return string|null
     */
    public function getLabelAlt(): ?string
    {
        if (null === $this->label_alt) {
            return null;
        }

        $altLabels = GeneralUtility::trimExplode(',', $this->label_alt);

        array_walk($altLabels, function (&$item) {
            $item = $this->tcaService->convertPropertyNameToColumnName($item);
        });

        return implode(', ', $altLabels);
    }

    /**
     * @return bool|null
     */
    public function getLabelAltForce(): ?bool
    {
        return $this->label_alt_force;
    }

    /**
     * @return string|null
     */
    public function getLabelUserFunc(): ?string
    {
        return $this->label_userFunc;
    }

    /**
     * @return string|null
     */
    public function getLanguageField(): ?string
    {
        return $this->languageField;
    }

    /**
     * @return string|null
     */
    public function getOrigUid(): ?string
    {
        return $this->origUid;
    }

    /**
     * @return string|null
     */
    public function getPrependAtCopy(): ?string
    {
        return $this->prependAtCopy;
    }

    /**
     * @return bool|null
     */
    public function getReadOnly(): ?bool
    {
        return $this->readOnly;
    }

    /**
     * @return int|null
     */
    public function getRootLevel(): ?int
    {
        return $this->rootLevel;
    }

    /**
     * @return string|null
     */
    public function getSearchFields(): ?string
    {
        if (null === $this->searchFields) {
            return null;
        }

        $searchFields = GeneralUtility::trimExplode(',', $this->searchFields);

        array_walk($searchFields, function (&$item) {
            $item = $this->tcaService->convertPropertyNameToColumnName($item);
        });

        return implode(', ', $searchFields);
    }

    /**
     * @return array|null
     */
    public function getSecurity(): ?array
    {
        return $this->security;
    }

    /**
     * @return string|null
     */
    public function getSeliconField(): ?string
    {
        return $this->selicon_field;
    }

    /**
     * @return string|null
     */
    public function getShadowColumnsForNewPlaceholders(): ?string
    {
        return $this->shadowColumnsForNewPlaceholders;
    }

    /**
     * @return string|null
     */
    public function getSortBy(): ?string
    {
        return $this->sortby;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getTransOrigDiffSourceField(): ?string
    {
        return $this->transOrigDiffSourceField;
    }

    /**
     * @return string|null
     */
    public function getTransOrigPointerField(): ?string
    {
        return $this->transOrigPointerField;
    }

    /**
     * @return string|null
     */
    public function getTranslationSource(): ?string
    {
        return $this->translationSource;
    }

    /**
     * @return string|null
     */
    public function getTstamp(): ?string
    {
        return $this->tstamp;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return array|null
     */
    public function getTypeiconClasses(): ?array
    {
        return $this->typeicon_classes;
    }

    /**
     * @return string|null
     */
    public function getTypeiconColumn(): ?string
    {
        if (null === $this->typeicon_column) {
            return null;
        }

        return $this->tcaService->convertPropertyNameToColumnName($this->typeicon_column);
    }

    /**
     * @return string|null
     */
    public function getUseColumnsForDefaultValues(): ?string
    {
        return $this->useColumnsForDefaultValues;
    }

    /**
     * @return bool|null
     */
    public function getVersioningWS(): ?bool
    {
        return $this->versioningWS;
    }

    /**
     * @return bool|null
     */
    public function getVersioningWSAlwaysAllowLiveEdit(): ?bool
    {
        return $this->versioningWS_alwaysAllowLiveEdit;
    }

    /**
     * @param bool|null $adminOnly
     *
     * @return void
     */
    public function setAdminOnly(?bool $adminOnly): void
    {
        $this->adminOnly = $adminOnly;
    }

    /**
     * @param array|null $container
     *
     * @return void
     */
    public function setContainer(?array $container): void
    {
        $this->container = $container;
    }

    /**
     * @param string|null $copyAfterDuplFields
     *
     * @return void
     */
    public function setCopyAfterDuplFields(?string $copyAfterDuplFields): void
    {
        $this->copyAfterDuplFields = $copyAfterDuplFields;
    }

    /**
     * @param string|null $crdate
     *
     * @return void
     */
    public function setCrdate(?string $crdate): void
    {
        $this->crdate = $crdate;
    }

    /**
     * @param string $cruser_id
     *
     * @return void
     */
    public function setCruserId(string $cruser_id): void
    {
        $this->cruser_id = $cruser_id;
    }

    /**
     * @param string|null $defaultSortBy
     *
     * @return void
     */
    public function setDefaultSortBy(?string $defaultSortBy): void
    {
        $this->default_sortby = $defaultSortBy;
    }

    /**
     * @param string|null $delete
     *
     * @return void
     */
    public function setDelete(?string $delete): void
    {
        $this->delete = $delete;
    }

    /**
     * @param string|null $descriptionColumn
     *
     * @return void
     */
    public function setDescriptionColumn(?string $descriptionColumn): void
    {
        $this->descriptionColumn = $descriptionColumn;
    }

    /**
     * @param array|null $EXT
     *
     * @return void
     */
    public function setEXT(?array $EXT): void
    {
        $this->EXT = $EXT;
    }

    /**
     * @param string|null $editlock
     *
     * @return void
     */
    public function setEditlock(?string $editlock): void
    {
        $this->editlock = $editlock;
    }

    /**
     * @param array|null $enablecolumns
     *
     * @return void
     */
    public function setEnablecolumns(?array $enablecolumns): void
    {
        $this->enablecolumns = $enablecolumns;
    }

    /**
     * @param string|null $formattedLabel_userFunc
     *
     * @return void
     */
    public function setFormattedLabelUserFunc(?string $formattedLabel_userFunc): void
    {
        $this->formattedLabel_userFunc = $formattedLabel_userFunc;
    }

    /**
     * @param array|null $formattedLabel_userFunc_options
     *
     * @return void
     */
    public function setFormattedLabelUserFuncOptions(?array $formattedLabel_userFunc_options): void
    {
        $this->formattedLabel_userFunc_options = $formattedLabel_userFunc_options;
    }

    /**
     * @param string|null $groupName
     *
     * @return void
     */
    public function setGroupName(?string $groupName): void
    {
        $this->groupName = $groupName;
    }

    /**
     * @param bool|null $hideAtCopy
     *
     * @return void
     */
    public function setHideAtCopy(?bool $hideAtCopy): void
    {
        $this->hideAtCopy = $hideAtCopy;
    }

    /**
     * @param bool|null $hideTable
     *
     * @return void
     */
    public function setHideTable(?bool $hideTable): void
    {
        $this->hideTable = $hideTable;
    }

    /**
     * @param string|null $iconfile
     *
     * @return void
     */
    public function setIconfile(?string $iconfile): void
    {
        $this->iconfile = $iconfile;
    }

    /**
     * @param bool|null $is_static
     *
     * @return void
     */
    public function setIsStatic(?bool $is_static): void
    {
        $this->is_static = $is_static;
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
     * @param string|null $label_alt
     *
     * @return void
     */
    public function setLabelAlt(?string $label_alt): void
    {
        $this->label_alt = $label_alt;
    }

    /**
     * @param bool|null $label_alt_force
     *
     * @return void
     */
    public function setLabelAltForce(?bool $label_alt_force): void
    {
        $this->label_alt_force = $label_alt_force;
    }

    /**
     * @param string|null $label_userFunc
     *
     * @return void
     */
    public function setLabelUserFunc(?string $label_userFunc): void
    {
        $this->label_userFunc = $label_userFunc;
    }

    /**
     * @param string|null $languageField
     *
     * @return void
     */
    public function setLanguageField(?string $languageField): void
    {
        $this->languageField = $languageField;
    }

    /**
     * @param string|null $origUid
     *
     * @return void
     */
    public function setOrigUid(?string $origUid): void
    {
        $this->origUid = $origUid;
    }

    /**
     * @param string|null $prependAtCopy
     *
     * @return void
     */
    public function setPrependAtCopy(?string $prependAtCopy): void
    {
        $this->prependAtCopy = $prependAtCopy;
    }

    /**
     * @param bool|null $readOnly
     *
     * @return void
     */
    public function setReadOnly(?bool $readOnly): void
    {
        $this->readOnly = $readOnly;
    }

    /**
     * @param int|null $rootLevel
     *
     * @return void
     */
    public function setRootLevel(?int $rootLevel): void
    {
        $this->rootLevel = $rootLevel;
    }

    /**
     * @param string|null $searchFields
     *
     * @return void
     */
    public function setSearchFields(?string $searchFields): void
    {
        $this->searchFields = $searchFields;
    }

    /**
     * @param array|null $security
     *
     * @return void
     */
    public function setSecurity(?array $security): void
    {
        $this->security = $security;
    }

    /**
     * @param string|null $selicon_field
     *
     * @return void
     */
    public function setSeliconField(?string $selicon_field): void
    {
        $this->selicon_field = $selicon_field;
    }

    /**
     * @param string|null $shadowColumnsForNewPlaceholders
     *
     * @return void
     */
    public function setShadowColumnsForNewPlaceholders(?string $shadowColumnsForNewPlaceholders): void
    {
        $this->shadowColumnsForNewPlaceholders = $shadowColumnsForNewPlaceholders;
    }

    /**
     * @param string|null $sortBy
     *
     * @return void
     */
    public function setSortBy(?string $sortBy): void
    {
        $this->setDefaultSortBy(null);
        $this->sortby = $sortBy;
    }

    /**
     * @param string|null $title
     *
     * @return void
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @param string|null $transOrigDiffSourceField
     *
     * @return void
     */
    public function setTransOrigDiffSourceField(?string $transOrigDiffSourceField): void
    {
        $this->transOrigDiffSourceField = $transOrigDiffSourceField;
    }

    /**
     * @param string|null $transOrigPointerField
     *
     * @return void
     */
    public function setTransOrigPointerField(?string $transOrigPointerField): void
    {
        $this->transOrigPointerField = $transOrigPointerField;
    }

    /**
     * @param string|null $translationSource
     *
     * @return void
     */
    public function setTranslationSource(?string $translationSource): void
    {
        $this->translationSource = $translationSource;
    }

    /**
     * @param string|null $tstamp
     *
     * @return void
     */
    public function setTstamp(?string $tstamp): void
    {
        $this->tstamp = $tstamp;
    }

    /**
     * @param string|null $type
     *
     * @return void
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    /**
     * @param array|null $typeicon_classes
     *
     * @return void
     */
    public function setTypeiconClasses(?array $typeicon_classes): void
    {
        $this->typeicon_classes = $typeicon_classes;
    }

    /**
     * @param string|null $typeicon_column
     *
     * @return void
     */
    public function setTypeiconColumn(?string $typeicon_column): void
    {
        $this->typeicon_column = $typeicon_column;
    }

    /**
     * @param string|null $useColumnsForDefaultValues
     *
     * @return void
     */
    public function setUseColumnsForDefaultValues(?string $useColumnsForDefaultValues): void
    {
        $this->useColumnsForDefaultValues = $useColumnsForDefaultValues;
    }

    /**
     * @param bool|null $versioningWS
     *
     * @return void
     */
    public function setVersioningWS(?bool $versioningWS): void
    {
        $this->versioningWS = $versioningWS;
    }

    /**
     * @param bool|null $versioningWS_alwaysAllowLiveEdit
     *
     * @return void
     */
    public function setVersioningWSAlwaysAllowLiveEdit(?bool $versioningWS_alwaysAllowLiveEdit): void
    {
        $this->versioningWS_alwaysAllowLiveEdit = $versioningWS_alwaysAllowLiveEdit;
    }
}
