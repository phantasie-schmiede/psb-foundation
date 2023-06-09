<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Attribute\TCA;

use Attribute;
use PSB\PsbFoundation\Utility\Configuration\TcaUtility;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class TcaConfig
 *
 * @link    https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Index.html
 * @package PSB\PsbFoundation\Attribute\TCA
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Ctrl extends AbstractTcaAttribute
{
    public const DEFAULT_SORTBY = 'uid DESC';

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
     * @param array|null        $EXT                              https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Ext.html
     * @param bool|null         $adminOnly                        https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/AdminOnly.html
     * @param array|null        $container                        https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Container.html
     * @param string|null       $copyAfterDuplFields              https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/CopyAfterDuplFields.html
     * @param string|null       $crdate                           https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Crdate.html
     * @param string|null       $defaultSortBy                    https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/DefaultSortby.html
     * @param string|null       $delete                           https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Delete.html
     * @param string|null       $descriptionColumn                https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/DescriptionColumn.html
     * @param string|null       $editLock                         https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Editlock.html
     * @param array|null        $enableColumns                    https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Enablecolumns.html
     * @param string|null       $formattedLabelUserFunc           https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/FormattedLabelUserFunc.html
     * @param array|null        $formattedLabelUserFuncOptions    https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/FormattedLabelUserFuncOptions.html
     * @param string|null       $groupName                        https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/GroupName.html
     * @param bool|null         $hideAtCopy                       https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/HideAtCopy.html
     * @param bool|null         $hideTable                        https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/HideTable.html
     * @param string|null       $iconFile                         https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Iconfile.html
     * @param bool|null         $ignorePageTypeRestriction        https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Security.html
     * @param bool|null         $ignoreRootLevelRestriction       https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Security.html
     * @param bool|null         $ignoreWebMountRestriction        https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Security.html
     * @param bool|null         $isStatic                         https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/IsStatic.html
     * @param string|null       $label                            https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Label.html
     * @param array|string|null $labelAlt                         https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Label.html
     * @param bool|null         $labelAltForce                    https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Label.html
     * @param string|null       $labelUserFunc                    https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/LabelUserfunc.html
     * @param string|null       $languageField                    https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/LanguageField.html
     * @param string|null       $origUid                          https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/OrigUid.html
     * @param string|null       $prependAtCopy                    https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/PrependAtCopy.html
     * @param bool|null         $readOnly                         https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/ReadOnly.html
     * @param int|null          $rootLevel                        https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/RootLevel.html
     * @param array|null        $searchFields                     https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/SearchFields.html
     * @param array|null        $security                         https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Security.html
     * @param string|null       $selIconField                     https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/SeliconField.html
     * @param string|null       $shadowColumnsForNewPlaceholders  https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/ShadowColumnsForNewPlaceholders.html
     * @param string|null       $sortBy                           https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Sortby.html
     * @param string|null       $title                            https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Title.html
     * @param string|null       $transOrigDiffSourceField         https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/TransOrigDiffSourceField.html
     * @param string|null       $transOrigPointerField            https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/TransOrigPointerField.html
     * @param string|null       $translationSource                https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/TranslationSource.html
     * @param string|null       $tstamp                           https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Tstamp.html
     * @param string|null       $type                             https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/Type.html
     * @param array|null        $typeIconClasses                  https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/TypeiconClasses.html
     * @param string|null       $typeIconColumn                   https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/TypeiconColumn.html
     * @param string|null       $useColumnsForDefaultValues       https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/UseColumnsForDefaultValues.html
     * @param bool|null         $versioningWS                     https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/VersioningWS.html
     * @param bool|null         $versioningWS_alwaysAllowLiveEdit https://docs.typo3.org/m/typo3/reference-tca/main/en-us/Ctrl/Properties/VersioningWSAlwaysAllowLiveEdit.html
     */
    public function __construct(
        protected ?array            $EXT = null,
        protected ?bool             $adminOnly = null,
        protected ?array            $container = null,
        protected ?string           $copyAfterDuplFields = null,
        protected ?string           $crdate = 'crdate',
        protected ?string           $defaultSortBy = self::DEFAULT_SORTBY,
        protected ?string           $delete = 'deleted',
        protected ?string           $descriptionColumn = null,
        protected ?string           $editLock = null,
        protected ?array            $enableColumns = self::ENABLE_COLUMNS,
        protected ?string           $formattedLabelUserFunc = null,
        protected ?array            $formattedLabelUserFuncOptions = null,
        protected ?string           $groupName = null,
        protected ?bool             $hideAtCopy = null,
        protected ?bool             $hideTable = null,
        protected ?string           $iconFile = 'EXT:core/Resources/Public/Icons/T3Icons/svgs/mimetypes/mimetypes-x-sys_action.svg',
        protected ?bool             $ignorePageTypeRestriction = null,
        protected ?bool             $ignoreRootLevelRestriction = null,
        protected ?bool             $ignoreWebMountRestriction = null,
        protected ?bool             $isStatic = null,
        /** You can use the property name. It will be converted to the column name automatically. */
        protected ?string           $label = 'uid',
        /** You can use property names. They will be converted to their column names automatically. */
        protected array|string|null $labelAlt = null,
        protected ?bool             $labelAltForce = null,
        protected ?string           $labelUserFunc = null,
        protected ?string           $languageField = 'sys_language_uid',
        protected ?string           $origUid = 't3_origuid',
        protected ?string           $prependAtCopy = null,
        protected ?bool             $readOnly = null,
        protected ?int              $rootLevel = null,
        protected ?array            $searchFields = null,
        protected ?array            $security = null,
        protected ?string           $selIconField = null,
        protected ?string           $shadowColumnsForNewPlaceholders = null,
        protected ?string           $sortBy = null,
        protected ?string           $title = null,
        protected ?string           $transOrigDiffSourceField = 'l10n_diffsource',
        protected ?string           $transOrigPointerField = 'l10n_parent',
        protected ?string           $translationSource = 'l10n_source',
        protected ?string           $tstamp = 'tstamp',
        protected ?string           $type = null,
        protected ?array            $typeIconClasses = null,
        protected ?string           $typeIconColumn = null,
        protected ?string           $useColumnsForDefaultValues = null,
        protected ?bool             $versioningWS = null,
        protected ?bool             $versioningWS_alwaysAllowLiveEdit = null,
    ) {
        parent::__construct();
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
     * @return string|null
     */
    public function getDefaultSortBy(): ?string
    {
        if (self::DEFAULT_SORTBY === $this->defaultSortBy && !empty($this->sortBy)) {
            return null;
        }

        return $this->defaultSortBy;
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
    public function getEditLock(): ?string
    {
        return $this->editLock;
    }

    /**
     * @return array|null
     */
    public function getEnableColumns(): ?array
    {
        return $this->enableColumns;
    }

    /**
     * @return string|null
     */
    public function getFormattedLabelUserFunc(): ?string
    {
        return $this->formattedLabelUserFunc;
    }

    /**
     * @return array|null
     */
    public function getFormattedLabelUserFuncOptions(): ?array
    {
        return $this->formattedLabelUserFuncOptions;
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
    public function getIconFile(): ?string
    {
        return $this->iconFile;
    }

    /**
     * @return bool|null
     */
    public function getIsStatic(): ?bool
    {
        return $this->isStatic;
    }

    /**
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function getLabel(): string
    {
        return $this->tcaService->convertPropertyNameToColumnName($this->label);
    }

    /**
     * @return string|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function getLabelAlt(): ?string
    {
        if (null === $this->labelAlt) {
            return null;
        }

        if (is_string($this->labelAlt)) {
            $altLabels = GeneralUtility::trimExplode(',', $this->labelAlt);
        } else {
            $altLabels = $this->labelAlt;
        }

        array_walk($altLabels, function(&$item) {
            $item = $this->tcaService->convertPropertyNameToColumnName($item);
        });

        return implode(', ', $altLabels);
    }

    /**
     * @return bool|null
     */
    public function getLabelAltForce(): ?bool
    {
        return $this->labelAltForce;
    }

    /**
     * @return string|null
     */
    public function getLabelUserFunc(): ?string
    {
        return $this->labelUserFunc;
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function getSearchFields(): ?string
    {
        if (null === $this->searchFields) {
            return null;
        }

        $searchFields = $this->searchFields;
        array_walk($searchFields, function(&$item) {
            $item = $this->tcaService->convertPropertyNameToColumnName($item);
        });

        return implode(', ', $searchFields);
    }

    /**
     * @return array|null
     */
    public function getSecurity(): ?array
    {
        $securityOptions = [
            'ignorePageTypeRestriction'  => $this->ignorePageTypeRestriction,
            'ignoreRootLevelRestriction' => $this->ignoreRootLevelRestriction,
            'ignoreWebMountRestriction'  => $this->ignoreWebMountRestriction,
        ];

        foreach ($securityOptions as $securityOption => $value) {
            if (null !== $value) {
                $this->security[$securityOption] = $value;
            }
        }

        return $this->security;
    }

    /**
     * @return string|null
     */
    public function getSelIconField(): ?string
    {
        return $this->selIconField;
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
        return $this->sortBy;
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
    public function getTypeIconClasses(): ?array
    {
        return $this->typeIconClasses;
    }

    /**
     * @return string|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function getTypeIconColumn(): ?string
    {
        if (null === $this->typeIconColumn) {
            return null;
        }

        return $this->tcaService->convertPropertyNameToColumnName($this->typeIconColumn);
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
     * @return array
     * @throws ReflectionException
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $result = [];

        foreach ($array as $key => $value) {
            $result[TcaUtility::convertKey($key)] = $value;
        }

        return $result;
    }
}
