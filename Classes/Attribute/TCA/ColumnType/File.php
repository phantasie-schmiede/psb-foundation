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
     * @param int|null     $maxitems                            https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/File/Properties/Maxitems.html
     * @param int|null     $minitems                            https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/File/Properties/Minitems.html
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
        protected ?int         $maxitems = null,
        protected ?int         $minitems = null,
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
    public function getMaxitems(): ?int
    {
        return $this->maxitems;
    }

    /**
     * @return int|null
     */
    public function getMinitems(): ?int
    {
        return $this->minitems;
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
            $configuration['fileNameGenerator']['properties'] = $this->uploadFileNameGeneratorProperties;

            if (null !== $this->uploadFileNameGeneratorAppendHash) {
                $configuration['fileNameGenerator']['appendHash'] = $this->uploadFileNameGeneratorAppendHash;
            }

            if (null !== $this->uploadFileNameGeneratorPrefix) {
                $configuration['fileNameGenerator']['prefix'] = $this->uploadFileNameGeneratorPrefix;
            }

            if (null !== $this->uploadFileNameGeneratorPropertySeparator) {
                $configuration['fileNameGenerator']['propertySeparator'] = $this->uploadFileNameGeneratorPropertySeparator;
            }

            if (null !== $this->uploadFileNameGeneratorReplacements) {
                $configuration['fileNameGenerator']['replacements'] = $this->uploadFileNameGeneratorReplacements;
            }

            if (null !== $this->uploadFileNameGeneratorSuffix) {
                $configuration['fileNameGenerator']['suffix'] = $this->uploadFileNameGeneratorSuffix;
            }
        }

        if (null !== $this->uploadTargetFolder) {
            $configuration['targetFolder'] = $this->uploadTargetFolder;
        }

        return $configuration;
    }
}
