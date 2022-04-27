<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Annotation\TCA\Column;

use ReflectionException;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class AbstractFalColumnAnnotation
 *
 * @package PSB\PsbFoundation\Annotation\TCA\Column
 */
class AbstractFalColumnAnnotation extends AbstractColumnAnnotation
{
    public const TYPE = self::TYPES['INLINE'];

    /**
     * @var string
     */
    protected string $allowedFileTypes = '';

    /**
     * @var array
     */
    protected array $appearance = [
        'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference',
    ];

    /**
     * @var int|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/11.5/en-us/ColumnsConfig/CommonProperties/Maxitems.html
     */
    protected ?int $maxItems = null;

    /**
     * @var int|null
     * @link https://docs.typo3.org/m/typo3/reference-tca/11.5/en-us/ColumnsConfig/CommonProperties/Minitems.html
     */
    protected ?int $minItems = null;

    /**
     * @return array
     */
    public function getAppearance(): array
    {
        return $this->appearance;
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
     * @param string $allowedFileTypes
     *
     * @return void
     */
    public function setAllowedFileTypes(string $allowedFileTypes): void
    {
        $this->allowedFileTypes = $allowedFileTypes;
    }

    /**
     * @param array $appearance
     *
     * @return void
     */
    public function setAppearance(array $appearance): void
    {
        $this->appearance = $appearance;
    }

    /**
     * @param int|null $maxItems
     *
     * @return void
     */
    public function setMaxItems(?int $maxItems): void
    {
        $this->maxItems = $maxItems;
    }

    /**
     * @param int|null $minItems
     *
     * @return void
     */
    public function setMinItems(?int $minItems): void
    {
        $this->minItems = $minItems;
    }

    /**
     * @param string $columnName
     *
     * @return array
     * @throws ReflectionException
     */
    public function toArray(string $columnName = ''): array
    {
        if ($this instanceof Image && Image::USE_CONFIGURATION_FILE_TYPES === $this->allowedFileTypes) {
            $this->allowedFileTypes = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
        }

        $fieldConfiguration = parent::toArray();
        $fieldConfiguration['config'] = ExtensionManagementUtility::getFileFieldTCAConfig($columnName,
            $fieldConfiguration['config'] ?? [], $this->allowedFileTypes);

        return $fieldConfiguration;
    }
}
