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

use PSB\PsbFoundation\Utility\Configuration\TcaUtility;
use ReflectionException;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class AbstractFalFieldAnnotation
 *
 * @package PSB\PsbFoundation\Annotation\TCA
 */
class AbstractFalFieldAnnotation extends AbstractFieldAnnotation
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

            if (in_array($key, ['displayCond', 'exclude', 'label'], true)) {
                $fieldConfiguration[$key] = $value;
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
