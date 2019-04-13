<?php
declare(strict_types=1);

namespace PS\PsFoundation\Services\Configuration;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 Daniel Ablass <dn@phantasie-schmiede.de>, Phantasie-Schmiede
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

use InvalidArgumentException;
use PS\PsFoundation\Exceptions\ImplementationException;
use PS\PsFoundation\Services\Configuration\ValueParsers\ValueParserInterface;
use PS\PsFoundation\Utilities\VariableUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FlexFormService
 * @package PS\PsFoundation\Services\Configuration
 */
class FlexFormService
{
    /**
     * @var string
     */
    private const DEFAULT_SHEET = 'sDEF';

    /**
     * @var string
     */
    private static $extensionKey;

    /**
     * @var string
     */
    private static $pluginName;

    /**
     * @var array
     */
    private static $valueParser = [];

    /**
     * @var string
     */
    protected $defaultLabelPath;

    /**
     * @var array
     */
    private $ds;

    /**
     * FlexFormService constructor.
     *
     * @param string $extensionKeyOrName
     * @param string $pluginName
     */
    public function __construct(string $extensionKeyOrName, string $pluginName)
    {
        self::setExtensionKey($extensionKeyOrName);
        self::setPluginName($pluginName);
        $this->setDefaultLabelPath('LLL:EXT:'.self::getExtensionKey().'/Resources/Private/Language/Backend/Configuration/FlexForms/'.lcfirst(self::getPluginName()).'.xlf:');
        $this->buildBasicStructure();
    }

    /**
     * @return string
     */
    public static function getExtensionKey(): string
    {
        return self::$extensionKey;
    }

    /**
     * @param string $extensionKeyOrName
     */
    public static function setExtensionKey(string $extensionKeyOrName): void
    {
        self::$extensionKey = GeneralUtility::camelCaseToLowerCaseUnderscored($extensionKeyOrName);
    }

    /**
     * @return string
     */
    public static function getPluginName(): string
    {
        return self::$pluginName;
    }

    /**
     * @param string $pluginName
     */
    public static function setPluginName(string $pluginName): void
    {
        self::$pluginName = $pluginName;
    }

    /**
     * @return string
     */
    public function getDefaultLabelPath(): string
    {
        return $this->defaultLabelPath;
    }

    /**
     * @param string $defaultLabelPath
     */
    public function setDefaultLabelPath(string $defaultLabelPath): void
    {
        $this->defaultLabelPath = $defaultLabelPath;
    }

    /**
     * @return array
     */
    public function getDs(): array
    {
        return $this->ds;
    }

    /**
     * @param array $ds
     */
    public function setDs(array $ds): void
    {
        $this->ds = $ds;
    }

    /**
     * @param ValueParserInterface $parser Instance of your custom parser class
     *
     * @throws \Exception
     */
    public static function addValueParser(ValueParserInterface $parser): void
    {
        if (!\defined(\get_class($parser).'::MARKER_TYPE')) {
            throw new ImplementationException(\get_class($parser).' has to define a constant named MARKER_TYPE!',
                1547211801);
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $markerType = $parser::MARKER_TYPE;
        self::$valueParser[$markerType] = $parser;
    }

    /**
     * @param string|null $dataStructure
     * @param string|null $extensionKeyOrName
     * @param string|null $pluginName
     */
    public static function register(
        string $dataStructure = null,
        string $extensionKeyOrName = null,
        string $pluginName = null
    ): void {
        if (null !== $extensionKeyOrName) {
            self::setExtensionKey(GeneralUtility::camelCaseToLowerCaseUnderscored($extensionKeyOrName));
        }

        if (null !== $pluginName) {
            self::setPluginName($pluginName);
        }

        $pluginKey = str_replace('_', '', self::getExtensionKey()).'_'.strtolower(self::getPluginName());

        if (null === $dataStructure) {
            $xmlPath = 'EXT:'.self::getExtensionKey().'/Configuration/FlexForms/'.self::getPluginName().'.xml';
            $xmlFile = file_get_contents(GeneralUtility::getFileAbsFileName($xmlPath));
            $dataStructure = self::replaceMarkers($xmlFile);
        }

        /** @noinspection UnsupportedStringOffsetOperationsInspection */
        $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginKey] = 'pi_flexform';
        ExtensionManagementUtility::addPiFlexFormValue($pluginKey, $dataStructure);
    }

    /**
     * @param string $name
     * @param string $type Use constant Fields::FIELD_TYPES for this argument
     * @param string $sheet
     * @param array  $customConfig
     * @param array  $customFieldConfiguration
     *
     * @return array
     */
    public function addField(
        string $name,
        string $type,
        string $sheet = self::DEFAULT_SHEET,
        $customConfig = [],
        $customFieldConfiguration = []
    ): array {
        $ds = $this->getDs();

        if (!isset($ds['T3DataStructure']['sheets'][$sheet])) {
            throw new InvalidArgumentException(__CLASS__.': No sheet with name "'.$sheet.'" registered in FlexForm!',
                1547470825);
        }

        $config = Fields::getDefaultConfiguration($type);
        ArrayUtility::mergeRecursiveWithOverrule($config, $customConfig);

        $fieldConfiguration = [
            'config' => $config,
            'label'  => $this->defaultLabelPath.$name,
        ];

        ArrayUtility::mergeRecursiveWithOverrule($fieldConfiguration, $customFieldConfiguration);
        $ds['T3DataStructure']['sheets'][$sheet]['ROOT']['el'][$name]['TCEforms'] = $fieldConfiguration;
        $this->setDs($ds);

        return $ds;
    }

    /**
     * @param string $name
     * @param null   $title
     */
    public function addSheet(string $name, $title = null): void
    {
        $ds = $this->getDs();

        $ds['T3DataStructure']['sheets'][$name] = [
            'ROOT' => [
                'el'       => [],
                'TCEforms' => [
                    'sheetTitle' => $title ?? $this->getDefaultLabelPath().'sheetTitle.'.$name,
                ],
                'type'     => 'array',
            ],
        ];

        $this->setDs($ds);
    }

    /**
     * @param array $items
     *
     * @return array
     */
    public function createSelectBoxItems(array $items): array
    {
        $index = 0;
        $preparedItems = ['_attributes' => ['type' => 'array']];

        foreach ($items as $label => $value) {
            $preparedItems[] = [
                'numIndex' => [
                    '_attributes' => [
                        'index' => $index++,
                        'type'  => 'array',
                    ],
                    [
                        'numIndex' => [
                            '_attributes' => [
                                'index' => 0,
                            ],
                            $label,
                        ],
                    ],
                    [
                        'numIndex' => [
                            '_attributes' => [
                                'index' => 1,
                            ],
                            $value,
                        ],
                    ],
                ],
            ];
        }

        return $preparedItems;
    }

    /**
     * @return string
     */
    public function getXml(): string
    {
        return VariableUtility::convertArrayToXml($this->getDs());
    }

    private function buildBasicStructure(): void
    {
        $this->setDs([
            'T3DataStructure' => [
                'meta'   => [
                    '_attributes' => [
                        'type' => 'array',
                    ],
                    'langDisable' => 1,
                ],
                'sheets' => [],
            ],
        ]);
        $this->addSheet(self::DEFAULT_SHEET);
    }

    /**
     * @param string $xml
     *
     * @return string
     */
    private static function replaceMarkers(string $xml): string
    {
        return preg_replace_callback('/###(.*):(.*)###/', function ($matches) {
            $replacement = '';
            [$markerType, $value] = [$matches[1], $matches[2]];

            if (isset(self::$valueParser[$markerType])) {
                $replacement = self::$valueParser[$markerType]->processValue($value);
            }

            return $replacement;
        }, $xml);
    }
}
