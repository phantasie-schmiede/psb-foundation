<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Annotation\TCA\Column;

use PSB\PsbFoundation\Utility\Configuration\TcaUtility;
use ReflectionException;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use function in_array;

/**
 * Class AbstractFalColumnAnnotation
 *
 * @package PSB\PsbFoundation\Annotation\TCA\Column
 */
class AbstractFalColumnAnnotation extends AbstractColumnAnnotation
{
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
     * @var int
     */
    protected int $maxItems = 0;

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

        $properties = parent::toArray();
        $fieldConfiguration = [];
        $fieldConfiguration['config'] = ExtensionManagementUtility::getFileFieldTCAConfig($columnName,
            [
                'appearance' => $this->getAppearance(),
                'maxitems'   => $this->getMaxItems(),
            ], $this->allowedFileTypes);

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

    /**
     * @param string $allowedFileTypes
     */
    public function setAllowedFileTypes(string $allowedFileTypes): void
    {
        $this->allowedFileTypes = $allowedFileTypes;
    }

    /**
     * @return array
     */
    public function getAppearance(): array
    {
        return $this->appearance;
    }

    /**
     * @param array $appearance
     */
    public function setAppearance(array $appearance): void
    {
        $this->appearance = $appearance;
    }

    /**
     * @return int
     */
    public function getMaxItems(): int
    {
        return $this->maxItems;
    }

    /**
     * @param int $maxItems
     */
    public function setMaxItems(int $maxItems): void
    {
        $this->maxItems = $maxItems;
    }
}
