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

namespace PSB\PsbFoundation\Service\DocComment\Annotations\TCA;

use PSB\PsbFoundation\Service\Configuration\TcaService;
use ReflectionException;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class AbstractTcaFalFieldAnnotation
 *
 * @package PSB\PsbFoundation\Service\DocComment\Annotations\TCA
 */
class AbstractTcaFalFieldAnnotation extends AbstractTcaFieldAnnotation
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
     * @return string
     */
    public function getAllowedFileTypes(): string
    {
        return $this->allowedFileTypes;
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

    /**
     * @param string $targetScope
     * @param string $columnName
     *
     * @return array
     * @throws ReflectionException
     */
    public function toArray(string $targetScope, string $columnName = ''): array
    {
        $properties = parent::toArray($targetScope);
        $fieldConfiguration = [];
        $fieldConfiguration['config'] = ExtensionManagementUtility::getFileFieldTCAConfig($columnName,
            [
                'appearance' => $this->getAppearance(),
                'maxitems'   => $this->getMaxItems(),
            ], $this->getAllowedFileTypes());

        foreach ($properties as $key => $value) {
            $key = TcaService::convertKey($key);

            if (in_array($key, ['displayCond', 'exclude', 'label'], true)) {
                $fieldConfiguration[$key] = $value;
            }
        }

        return $fieldConfiguration;
    }
}
