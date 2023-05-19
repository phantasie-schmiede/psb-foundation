<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Attribute\TCA\ColumnType;

use Attribute;
use PSB\PsbFoundation\Data\ExtensionInformation;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class File
 *
 * @package PSB\PsbFoundation\Attribute\TCA\ColumnType
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class File extends AbstractColumnType
{
    public const DATABASE_DEFINITION = 'int(11) unsigned DEFAULT \'0\'';

    /**
     * @param array|string $allowed                             https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/File/Properties/Allowed.html
     * @param int|null     $maxItems                            https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/File/Properties/Maxitems.html
     * @param int|null     $minItems                            https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/File/Properties/Minitems.html
     * @param array|null   $overrideChildTca                    https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/File/Properties/OverrideChildTCa.html
     * @param array|null   $upload
     * @param bool         $uploadFileNameGeneratorAppendHash
     * @param string|null  $uploadFileNameGeneratorPrefix
     * @param string       $uploadFileNameGeneratorPropertySeparator
     * @param array|null   $uploadFileNameGeneratorProperties   Mandatory property for other file name generator
     *                                                          options!
     * @param array|null   $uploadFileNameGeneratorReplacements Associative array whose keys will be replaced by its
     *                                                          values in the file name
     * @param string|null  $uploadFileNameGeneratorSuffix
     * @param string|null  $uploadTargetFolder
     */
    public function __construct(
        protected array|string $allowed = 'common-image-types',
        protected ?int         $maxItems = null,
        protected ?int         $minItems = null,
        protected ?array       $overrideChildTca = null,
        protected ?array       $upload = null,
        protected bool         $uploadFileNameGeneratorAppendHash = true,
        protected ?string      $uploadFileNameGeneratorPrefix = null,
        protected string       $uploadFileNameGeneratorPropertySeparator = '-',
        protected ?array       $uploadFileNameGeneratorProperties = null,
        protected ?array       $uploadFileNameGeneratorReplacements = null,
        protected ?string      $uploadFileNameGeneratorSuffix = null,
        protected ?string      $uploadTargetFolder = null,
    ) {
    }

    /**
     * @return array|string
     */
    public function getAllowed(): array|string
    {
        return $this->allowed;
    }

    /**
     * @return int|null
     */
    public function getMaxItems(): ?int
    {
        return $this->maxItems;
    }

    /**
     * @return int|null
     */
    public function getMinItems(): ?int
    {
        return $this->minItems;
    }

    /**
     * @return array|null
     */
    public function getOverrideChildTca(): ?array
    {
        return $this->overrideChildTca;
    }

    /**
     * @return array|null
     */
    public function getUpload(): ?array
    {
        $configuration = null;

        if (null !== $this->uploadFileNameGeneratorProperties) {
            $fileNameGeneratorOptions['properties'] = $this->uploadFileNameGeneratorProperties;

            if (null !== $this->uploadFileNameGeneratorAppendHash) {
                $fileNameGeneratorOptions['appendHash'] = $this->uploadFileNameGeneratorAppendHash;
            }

            if (null !== $this->uploadFileNameGeneratorPrefix) {
                $fileNameGeneratorOptions['prefix'] = $this->uploadFileNameGeneratorPrefix;
            }

            if (null !== $this->uploadFileNameGeneratorPropertySeparator) {
                $fileNameGeneratorOptions['propertySeparator'] = $this->uploadFileNameGeneratorPropertySeparator;
            }

            if (null !== $this->uploadFileNameGeneratorReplacements) {
                $fileNameGeneratorOptions['replacements'] = $this->uploadFileNameGeneratorReplacements;
            }

            if (null !== $this->uploadFileNameGeneratorSuffix) {
                $fileNameGeneratorOptions['suffix'] = $this->uploadFileNameGeneratorSuffix;
            }

            $configuration['fileNameGenerator'] = $fileNameGeneratorOptions;
        }

        if (null !== $this->uploadTargetFolder) {
            $configuration['targetFolder'] = $this->uploadTargetFolder;
        }

        if (empty($configuration)) {
            return null;
        }

        $extensionInformation = GeneralUtility::makeInstance(ExtensionInformation::class);

        return ['EXT' => [$extensionInformation->getExtensionKey() => ['upload' => $configuration]]];
    }
}
