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

namespace PSB\PsbFoundation\Service\Configuration;

use Exception;
use InvalidArgumentException;
use PSB\PsbFoundation\Exceptions\ImplementationException;
use PSB\PsbFoundation\Service\Configuration\ValueParsers\ValueParserInterface;
use PSB\PsbFoundation\Utility\Xml\XmlUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use function defined;
use function get_class;

/**
 * Class FlexFormService
 *
 * This service allows you to dynamically build and/or register FlexForms for your plugins.
 *
 * @package PSB\PsbFoundation\Service\Configuration
 */
class FlexFormService
{
    /**
     * @var string
     */
    public const DEFAULT_SHEET = 'sDEF';

    /**
     * @var string
     */
    private static string $extensionKey = '';

    /**
     * @var string
     */
    private static string $pluginName = '';

    /**
     * @var array
     */
    private static array $valueParser = [];

    /**
     * @var string
     */
    protected string $defaultLabelPath = '';

    /**
     * @var array
     */
    private array $ds = [];

    /**
     * @param string $extensionKeyOrName
     * @param string $pluginName
     */
    public function __construct(string $extensionKeyOrName, string $pluginName)
    {
        self::setExtensionKey($extensionKeyOrName);
        self::setPluginName($pluginName);
        $this->setDefaultLabelPath('LLL:EXT:' . self::getExtensionKey() . '/Resources/Private/Language/Backend/Configuration/FlexForms/' . lcfirst(self::getPluginName()) . '.xlf:');
        $this->buildBasicStructure();
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
    public function getXml(): string
    {
        return XmlUtility::convertToXml($this->getDs());
    }

    /**
     * @param ValueParserInterface $parser Instance of your custom parser class
     *
     * @throws Exception
     */
    public static function addValueParser(ValueParserInterface $parser): void
    {
        if (!defined(get_class($parser) . '::MARKER_TYPE')) {
            throw new ImplementationException(get_class($parser) . ' has to define a constant named MARKER_TYPE!',
                1547211801);
        }

        $markerType = $parser::MARKER_TYPE;
        self::$valueParser[$markerType] = $parser;
    }

    /**
     * Transforms key-value pairs into FlexForm-compatible options, whereas the key is the visible label. The return
     * value of this function can be used as the value of the field config key 'items' of a select field.
     *
     * @param array $items
     *
     * @return array
     */
    public static function createSelectBoxItems(array $items): array
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
     * Registers a FlexForm's data structure in XML-format to a plugin
     *
     * @param string|null $dataStructure
     * @param string|null $extensionKeyOrName
     * @param string|null $pluginName
     *
     * @see getXML()
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

        $pluginKey = str_replace('_', '', self::getExtensionKey()) . '_' . mb_strtolower(self::getPluginName());

        if (null === $dataStructure) {
            $xmlPath = 'EXT:' . self::getExtensionKey() . '/Configuration/FlexForms/' . self::getPluginName() . '.xml';
            $xmlFile = file_get_contents(GeneralUtility::getFileAbsFileName($xmlPath));
            $dataStructure = self::replaceMarkers($xmlFile);
        }

        $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginKey] = 'pi_flexform';
        ExtensionManagementUtility::addPiFlexFormValue($pluginKey, $dataStructure);
    }

    /**
     * This method is only called, when registering a FlexForm from a file in default location. There is no need to use
     * special markers if you dynamically build the FlexForm using the other functions of this service class.
     *
     * @param string $xml
     *
     * @return string
     */
    private static function replaceMarkers(string $xml): string
    {
        return preg_replace_callback('/###(.*):(.*)###/', static function ($matches) {
            $replacement = '';
            [$markerType, $value] = [$matches[1], $matches[2]];

            if (isset(self::$valueParser[$markerType])) {
                $replacement = self::$valueParser[$markerType]->processValue($value);
            }

            return $replacement;
        }, $xml);
    }

    /**
     * @param string $name
     * @param string $type Use constant \PSB\PsbFoundation\Service\Configuration\Fields::FIELD_TYPES for this argument
     * @param string $sheet
     * @param array  $customConfig
     * @param array  $customFieldConfiguration
     *
     * @return array
     * @see \PSB\PsbFoundation\Service\Configuration\Fields
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
            throw new InvalidArgumentException(__CLASS__ . ': No sheet with name "' . $sheet . '" registered in FlexForm!',
                1547470825);
        }

        // @TODO: Refactor this whole service!
//        $config = Fields::getDefaultConfiguration($type);
        ArrayUtility::mergeRecursiveWithOverrule($config, $customConfig);

        $fieldConfiguration = [
            'config' => $config,
            'label'  => $this->defaultLabelPath . $name,
        ];

        ArrayUtility::mergeRecursiveWithOverrule($fieldConfiguration, $customFieldConfiguration);
        $ds['T3DataStructure']['sheets'][$sheet]['ROOT']['el'][$name]['TCEforms'] = $fieldConfiguration;
        $this->setDs($ds);

        return $ds;
    }

    /**
     * Adds a new tab to the FlexForm. New fields can be added to that tab then.
     *
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
                    'sheetTitle' => $title ?? $this->getDefaultLabelPath() . 'sheetTitle.' . $name,
                ],
                'type'     => 'array',
            ],
        ];

        $this->setDs($ds);
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
}
