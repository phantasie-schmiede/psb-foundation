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
    public const ENABLE_COLUMN_IDENTIFIERS = [
        'DISABLED'  => 'disabled',
        'ENDTIME'   => 'endtime',
        'STARTTIME' => 'starttime',
    ];

    public const ENABLE_COLUMNS = [
        self::ENABLE_COLUMN_IDENTIFIERS['DISABLED']  => 'hidden',
        self::ENABLE_COLUMN_IDENTIFIERS['ENDTIME']   => 'endtime',
        self::ENABLE_COLUMN_IDENTIFIERS['STARTTIME'] => 'starttime',
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
    protected ?string $defaultSortBy = 'uid DESC';

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
     * @return bool|null
     */
    public function getAdminOnly(): ?bool
    {
        return $this->adminOnly;
    }

    /**
     * @param bool|null $adminOnly
     */
    public function setAdminOnly(?bool $adminOnly): void
    {
        $this->adminOnly = $adminOnly;
    }

    /**
     * @return array|null
     */
    public function getContainer(): ?array
    {
        return $this->container;
    }

    /**
     * @param array|null $container
     */
    public function setContainer(?array $container): void
    {
        $this->container = $container;
    }

    /**
     * @return string|null
     */
    public function getCopyAfterDuplFields(): ?string
    {
        return $this->copyAfterDuplFields;
    }

    /**
     * @param string|null $copyAfterDuplFields
     */
    public function setCopyAfterDuplFields(?string $copyAfterDuplFields): void
    {
        $this->copyAfterDuplFields = $copyAfterDuplFields;
    }

    /**
     * @return string|null
     */
    public function getCrdate(): ?string
    {
        return $this->crdate;
    }

    /**
     * @param string|null $crdate
     */
    public function setCrdate(?string $crdate): void
    {
        $this->crdate = $crdate;
    }

    /**
     * @return string
     */
    public function getCruserId(): string
    {
        return $this->cruser_id;
    }

    /**
     * @param string $cruser_id
     */
    public function setCruserId(string $cruser_id): void
    {
        $this->cruser_id = $cruser_id;
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
     * @return string|null
     */
    public function getDelete(): ?string
    {
        return $this->delete;
    }

    /**
     * @param string|null $delete
     */
    public function setDelete(?string $delete): void
    {
        $this->delete = $delete;
    }

    /**
     * @return string|null
     */
    public function getDescriptionColumn(): ?string
    {
        return $this->descriptionColumn;
    }

    /**
     * @param string|null $descriptionColumn
     */
    public function setDescriptionColumn(?string $descriptionColumn): void
    {
        $this->descriptionColumn = $descriptionColumn;
    }

    /**
     * @return array|null
     */
    public function getEXT(): ?array
    {
        return $this->EXT;
    }

    /**
     * @param array|null $EXT
     */
    public function setEXT(?array $EXT): void
    {
        $this->EXT = $EXT;
    }

    /**
     * @return string|null
     */
    public function getEditlock(): ?string
    {
        return $this->editlock;
    }

    /**
     * @param string|null $editlock
     */
    public function setEditlock(?string $editlock): void
    {
        $this->editlock = $editlock;
    }

    /**
     * @return array|null
     */
    public function getEnablecolumns(): ?array
    {
        return $this->enablecolumns;
    }

    /**
     * @param array|null $enablecolumns
     */
    public function setEnablecolumns(?array $enablecolumns): void
    {
        $this->enablecolumns = $enablecolumns;
    }

    /**
     * @return string|null
     */
    public function getFormattedLabelUserFunc(): ?string
    {
        return $this->formattedLabel_userFunc;
    }

    /**
     * @param string|null $formattedLabel_userFunc
     */
    public function setFormattedLabelUserFunc(?string $formattedLabel_userFunc): void
    {
        $this->formattedLabel_userFunc = $formattedLabel_userFunc;
    }

    /**
     * @return array|null
     */
    public function getFormattedLabelUserFuncOptions(): ?array
    {
        return $this->formattedLabel_userFunc_options;
    }

    /**
     * @param array|null $formattedLabel_userFunc_options
     */
    public function setFormattedLabelUserFuncOptions(?array $formattedLabel_userFunc_options): void
    {
        $this->formattedLabel_userFunc_options = $formattedLabel_userFunc_options;
    }

    /**
     * @return string|null
     */
    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    /**
     * @param string|null $groupName
     */
    public function setGroupName(?string $groupName): void
    {
        $this->groupName = $groupName;
    }

    /**
     * @return bool|null
     */
    public function getHideAtCopy(): ?bool
    {
        return $this->hideAtCopy;
    }

    /**
     * @param bool|null $hideAtCopy
     */
    public function setHideAtCopy(?bool $hideAtCopy): void
    {
        $this->hideAtCopy = $hideAtCopy;
    }

    /**
     * @return bool|null
     */
    public function getHideTable(): ?bool
    {
        return $this->hideTable;
    }

    /**
     * @param bool|null $hideTable
     */
    public function setHideTable(?bool $hideTable): void
    {
        $this->hideTable = $hideTable;
    }

    /**
     * @return string|null
     */
    public function getIconfile(): ?string
    {
        return $this->iconfile;
    }

    /**
     * @param string|null $iconfile
     */
    public function setIconfile(?string $iconfile): void
    {
        $this->iconfile = $iconfile;
    }

    /**
     * @return bool|null
     */
    public function getIsStatic(): ?bool
    {
        return $this->is_static;
    }

    /**
     * @param bool|null $is_static
     */
    public function setIsStatic(?bool $is_static): void
    {
        $this->is_static = $is_static;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
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
        if (null === $this->label_alt) {
            return null;
        }

        $altLabels = GeneralUtility::trimExplode(',', $this->label_alt);

        array_walk($altLabels, function (&$item) {
            $item = $this->tcaService->convertPropertyNameToColumnName($item);
        });

        return implode(',', $altLabels);
    }

    /**
     * @param string|null $label_alt
     */
    public function setLabelAlt(?string $label_alt): void
    {
        $this->label_alt = $label_alt;
    }

    /**
     * @return bool|null
     */
    public function getLabelAltForce(): ?bool
    {
        return $this->label_alt_force;
    }

    /**
     * @param bool|null $label_alt_force
     */
    public function setLabelAltForce(?bool $label_alt_force): void
    {
        $this->label_alt_force = $label_alt_force;
    }

    /**
     * @return string|null
     */
    public function getLabelUserFunc(): ?string
    {
        return $this->label_userFunc;
    }

    /**
     * @param string|null $label_userFunc
     */
    public function setLabelUserFunc(?string $label_userFunc): void
    {
        $this->label_userFunc = $label_userFunc;
    }

    /**
     * @return string|null
     */
    public function getLanguageField(): ?string
    {
        return $this->languageField;
    }

    /**
     * @param string|null $languageField
     */
    public function setLanguageField(?string $languageField): void
    {
        $this->languageField = $languageField;
    }

    /**
     * @return string|null
     */
    public function getOrigUid(): ?string
    {
        return $this->origUid;
    }

    /**
     * @param string|null $origUid
     */
    public function setOrigUid(?string $origUid): void
    {
        $this->origUid = $origUid;
    }

    /**
     * @return string|null
     */
    public function getPrependAtCopy(): ?string
    {
        return $this->prependAtCopy;
    }

    /**
     * @param string|null $prependAtCopy
     */
    public function setPrependAtCopy(?string $prependAtCopy): void
    {
        $this->prependAtCopy = $prependAtCopy;
    }

    /**
     * @return bool|null
     */
    public function getReadOnly(): ?bool
    {
        return $this->readOnly;
    }

    /**
     * @param bool|null $readOnly
     */
    public function setReadOnly(?bool $readOnly): void
    {
        $this->readOnly = $readOnly;
    }

    /**
     * @return int|null
     */
    public function getRootLevel(): ?int
    {
        return $this->rootLevel;
    }

    /**
     * @param int|null $rootLevel
     */
    public function setRootLevel(?int $rootLevel): void
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
     * @return array|null
     */
    public function getSecurity(): ?array
    {
        return $this->security;
    }

    /**
     * @param array|null $security
     */
    public function setSecurity(?array $security): void
    {
        $this->security = $security;
    }

    /**
     * @return string|null
     */
    public function getSeliconField(): ?string
    {
        return $this->selicon_field;
    }

    /**
     * @param string|null $selicon_field
     */
    public function setSeliconField(?string $selicon_field): void
    {
        $this->selicon_field = $selicon_field;
    }

    /**
     * Function name doesn't follow convention to exclude it from array conversion (see toArray())!
     *
     * @return string[]
     */
    public function getExplicitlySetProperties(): array
    {
        return $this->_setProperties;
    }

    /**
     * @return string|null
     */
    public function getShadowColumnsForNewPlaceholders(): ?string
    {
        return $this->shadowColumnsForNewPlaceholders;
    }

    /**
     * @param string|null $shadowColumnsForNewPlaceholders
     */
    public function setShadowColumnsForNewPlaceholders(?string $shadowColumnsForNewPlaceholders): void
    {
        $this->shadowColumnsForNewPlaceholders = $shadowColumnsForNewPlaceholders;
    }

    /**
     * @return string|null
     */
    public function getSortBy(): ?string
    {
        return $this->sortby;
    }

    /**
     * @param string|null $sortBy
     */
    public function setSortBy(?string $sortBy): void
    {
        $this->setDefaultSortBy(null);
        $this->sortby = $sortBy;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getTransOrigDiffSourceField(): ?string
    {
        return $this->transOrigDiffSourceField;
    }

    /**
     * @param string|null $transOrigDiffSourceField
     */
    public function setTransOrigDiffSourceField(?string $transOrigDiffSourceField): void
    {
        $this->transOrigDiffSourceField = $transOrigDiffSourceField;
    }

    /**
     * @return string|null
     */
    public function getTransOrigPointerField(): ?string
    {
        return $this->transOrigPointerField;
    }

    /**
     * @param string|null $transOrigPointerField
     */
    public function setTransOrigPointerField(?string $transOrigPointerField): void
    {
        $this->transOrigPointerField = $transOrigPointerField;
    }

    /**
     * @return string|null
     */
    public function getTranslationSource(): ?string
    {
        return $this->translationSource;
    }

    /**
     * @param string|null $translationSource
     */
    public function setTranslationSource(?string $translationSource): void
    {
        $this->translationSource = $translationSource;
    }

    /**
     * @return string|null
     */
    public function getTstamp(): ?string
    {
        return $this->tstamp;
    }

    /**
     * @param string|null $tstamp
     */
    public function setTstamp(?string $tstamp): void
    {
        $this->tstamp = $tstamp;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return array|null
     */
    public function getTypeiconClasses(): ?array
    {
        return $this->typeicon_classes;
    }

    /**
     * @param array|null $typeicon_classes
     */
    public function setTypeiconClasses(?array $typeicon_classes): void
    {
        $this->typeicon_classes = $typeicon_classes;
    }

    /**
     * @return string|null
     */
    public function getTypeiconColumn(): ?string
    {
        return $this->typeicon_column;
    }

    /**
     * @param string|null $typeicon_column
     */
    public function setTypeiconColumn(?string $typeicon_column): void
    {
        $this->typeicon_column = $typeicon_column;
    }

    /**
     * @return string|null
     */
    public function getUseColumnsForDefaultValues(): ?string
    {
        return $this->useColumnsForDefaultValues;
    }

    /**
     * @param string|null $useColumnsForDefaultValues
     */
    public function setUseColumnsForDefaultValues(?string $useColumnsForDefaultValues): void
    {
        $this->useColumnsForDefaultValues = $useColumnsForDefaultValues;
    }

    /**
     * @return bool|null
     */
    public function getVersioningWS(): ?bool
    {
        return $this->versioningWS;
    }

    /**
     * @param bool|null $versioningWS
     */
    public function setVersioningWS(?bool $versioningWS): void
    {
        $this->versioningWS = $versioningWS;
    }

    /**
     * @return bool|null
     */
    public function getVersioningWSAlwaysAllowLiveEdit(): ?bool
    {
        return $this->versioningWS_alwaysAllowLiveEdit;
    }

    /**
     * @param bool|null $versioningWS_alwaysAllowLiveEdit
     */
    public function setVersioningWSAlwaysAllowLiveEdit(?bool $versioningWS_alwaysAllowLiveEdit): void
    {
        $this->versioningWS_alwaysAllowLiveEdit = $versioningWS_alwaysAllowLiveEdit;
    }
}
