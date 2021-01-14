<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Service\DocComment\Annotations\TCA;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020-2021 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

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
