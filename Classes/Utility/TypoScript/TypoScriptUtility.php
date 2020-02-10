<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Utility\TypoScript;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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

use PSB\PsbFoundation\Data\ExtensionInformationInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use UnexpectedValueException;

/**
 * Class TypoScriptUtility
 * @package PSB\PsbFoundation\Utility
 */
class TypoScriptUtility
{
    public const COMPONENTS = [
        'MODULE' => 'module',
        'PLUGIN' => 'plugin',
    ];

    public const FILE_ENDING = '.typoscript';

    public const FILE_NAMES = [
        'CONSTANTS' => 'constants',
        'SETUP'     => 'setup',
    ];

    public const INDENTATION = '    ';

    public const TYPO_SCRIPT_KEYS = [
        'COMMENT'     => '_comment',
        'CONDITION'   => '_condition',
        'IMPORT'      => '_import',
        'OBJECT_TYPE' => '_objectType',
    ];

    /**
     * @var string
     */
    private static $lineBreakAfterCurlyBracketClose = '';

    /**
     * @var string
     */
    private static $lineBreakBeforeCurlyBracketOpen = '';

    /**
     * @var string
     */
    private static $objectPath = '';

    /**
     * For use in Classes/Slots/Setup.php files
     *
     * @param ExtensionInformationInterface $extensionInformation
     */
    public static function addDefaultTypoScriptForPluginsAndModules(ExtensionInformationInterface $extensionInformation): void
    {
        if (!empty($extensionInformation->getPlugins())) {
            $constantsArray = self::getDefaultConstants($extensionInformation, self::COMPONENTS['PLUGIN']);
            $constantsTypoScript = self::convertArrayToTypoScript($constantsArray);
            $setupArray = self::getDefaultSetup($extensionInformation, self::COMPONENTS['PLUGIN']);
            $setupTypoScript = self::convertArrayToTypoScript($setupArray);
        }

        if (!empty($extensionInformation->getModules())) {
            if (!empty($extensionInformation->getPlugins())) {
                $key = 'tx_' . mb_strtolower($extensionInformation->getExtensionName());
                $setupArray = [
                    self::COMPONENTS['MODULE'] => [
                        $key => [
                            self::TYPO_SCRIPT_KEYS['IMPORT'] => self::COMPONENTS['PLUGIN'] . '.' . $key,
                        ],
                    ],
                ];
                /** @noinspection PhpUndefinedVariableInspection */
                $setupTypoScript .= LF . self::convertArrayToTypoScript($setupArray);
            } else {
                $constantsArray = self::getDefaultConstants($extensionInformation, self::COMPONENTS['MODULE']);
                $constantsTypoScript = self::convertArrayToTypoScript($constantsArray);
                $setupArray = self::getDefaultSetup($extensionInformation, self::COMPONENTS['MODULE']);
                $setupTypoScript = self::convertArrayToTypoScript($setupArray);
            }
        }

        $typoScriptDirectory = Environment::getExtensionsPath() . '/' . $extensionInformation->getExtensionKey() . '/Configuration/TypoScript/';

        if (isset($constantsTypoScript) && !file_exists($typoScriptDirectory . self::FILE_NAMES['CONSTANTS'] . self::FILE_ENDING)) {
            if (!is_dir($typoScriptDirectory)) {
                GeneralUtility::mkdir_deep($typoScriptDirectory);
            }

            GeneralUtility::writeFile($typoScriptDirectory . self::FILE_NAMES['CONSTANTS'] . self::FILE_ENDING,
                $constantsTypoScript, true);
        }

        if (isset($setupTypoScript) && !file_exists($typoScriptDirectory . self::FILE_NAMES['SETUP'] . self::FILE_ENDING)) {
            if (!is_dir($typoScriptDirectory)) {
                GeneralUtility::mkdir_deep($typoScriptDirectory);
            }

            GeneralUtility::writeFile($typoScriptDirectory . self::FILE_NAMES['SETUP'] . self::FILE_ENDING,
                $setupTypoScript, true);
        }
    }

    /**
     * @param array $array
     *
     * @return string
     */
    public static function convertArrayToTypoScript(array $array): string
    {
        if (GeneralUtility::getApplicationContext()->isDevelopment()) {
            $backtrace = debug_backtrace();
            $debugInformation = [
                'class'    => $backtrace[1]['class'],
                'function' => $backtrace[1]['function'],
                'line'     => $backtrace[0]['line'],
            ];
            $debugOutput = '// TypoScript generated by ' . $debugInformation['class'] . ':' . $debugInformation['function'] . ' in line ' . $debugInformation['line'] . LF;
        }

        $generatedTypoScript = self::buildTypoScriptFromArray($array);

        // reset formatting helpers
        self::resetLineBreaks();
        self::resetObjectPath();

        return ($debugOutput ?? '') . $generatedTypoScript;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public static function getPreparedTypoScriptConstant(string $key): string
    {
        return '{$' . $key . '}';
    }

    /**
     * @param PageObjectConfiguration $pageTypeConfiguration
     *
     * @return string
     */
    public static function registerNewPageObject(PageObjectConfiguration $pageTypeConfiguration): string
    {
        $typoScript = [
            self::TYPO_SCRIPT_KEYS['CONDITION']               => 'globalVar = TSFE:type = ' . $pageTypeConfiguration->getTypeNum(),
            $pageTypeConfiguration->getTypoScriptObjectName() => [
                self::TYPO_SCRIPT_KEYS['OBJECT_TYPE'] => 'PAGE',
                'config'                              => [
                    'additionalHeaders'    => [
                        10 => [
                            'header' => 'Content-type: ' . $pageTypeConfiguration->getContentType(),
                        ],
                    ],
                    'debug'                => 0,
                    'disableAllHeaderCode' => 1,
                    'sys_language_mode'    => 'ignore',
                ],
                'typeNum'                             => $pageTypeConfiguration->getTypeNum(),
                10                                    => [
                    self::TYPO_SCRIPT_KEYS['OBJECT_TYPE'] => 'USER_INT',
                    'action'                              => $pageTypeConfiguration->getAction(),
                    'controller'                          => $pageTypeConfiguration->getController(),
                    'extensionName'                       => $pageTypeConfiguration->getExtensionName(),
                    'pluginName'                          => $pageTypeConfiguration->getPluginName(),
                    'settings'                            => $pageTypeConfiguration->getSettings(),
                    'switchableControllerActions'         => [
                        $pageTypeConfiguration->getController() => [
                            1 => $pageTypeConfiguration->getAction(),
                        ],
                    ],
                    'userFunc'                            => 'TYPO3\CMS\Extbase\Core\Bootstrap->run',
                    'vendorName'                          => $pageTypeConfiguration->getVendorName(),
                ],
            ],
        ];

        return self::convertArrayToTypoScript($typoScript);
    }

    /**
     * For use in Configuration/TCA/Overrides/sys_template.php files
     *
     * @param ExtensionInformationInterface $extensionInformation
     * @param string                        $path
     * @param string                        $title
     */
    public static function registerTypoScript(
        ExtensionInformationInterface $extensionInformation,
        string $path = 'Configuration/TypoScript',
        string $title = 'Main configuration'
    ): void {
        ExtensionManagementUtility::addStaticFile($extensionInformation->getExtensionKey(), $path, $title);
    }

    /**
     * @param array $array
     * @param int   $indentationLevel
     *
     * @return string
     */
    private static function buildTypoScriptFromArray(array $array, int $indentationLevel = 0): string
    {
        ksort($array);
        $typoScript = '';

        if (isset($array[self::TYPO_SCRIPT_KEYS['CONDITION']])) {
            if (0 < $indentationLevel) {
                throw new UnexpectedValueException(__CLASS__ . ': TypoScript conditions must not be placed inside nested elements!',
                    1552992577);
            }

            $typoScript .= '[' . $array[self::TYPO_SCRIPT_KEYS['CONDITION']] . ']' . LF;
            unset ($array[self::TYPO_SCRIPT_KEYS['CONDITION']]);
            $typoScript .= self::buildTypoScriptFromArray($array, $indentationLevel);
            $typoScript .= '[GLOBAL]' . LF;
        } else {
            foreach ($array as $key => $value) {
                $indentation = self::createIndentation($indentationLevel);

                if (is_array($value) && isset($value[self::TYPO_SCRIPT_KEYS['COMMENT']])) {
                    if (is_array($value[self::TYPO_SCRIPT_KEYS['COMMENT']])) {
                        $typoScript .= (self::$lineBreakAfterCurlyBracketClose ? : self::$lineBreakBeforeCurlyBracketOpen);

                        foreach ($value[self::TYPO_SCRIPT_KEYS['COMMENT']] as $commentLine) {
                            $typoScript .= $indentation . '# ' . $commentLine . LF;
                        }
                    } else {
                        $typoScript .= (self::$lineBreakAfterCurlyBracketClose ? : self::$lineBreakBeforeCurlyBracketOpen) . $indentation . '# ' . $value[self::TYPO_SCRIPT_KEYS['COMMENT']] . LF;
                    }
                    self::$lineBreakBeforeCurlyBracketOpen = '';
                    unset($value[self::TYPO_SCRIPT_KEYS['COMMENT']]);

                    if (isset($value[0])) {
                        $value = array_shift($value);
                    }
                }

                if (is_array($value)) {
                    if (isset($value[self::TYPO_SCRIPT_KEYS['OBJECT_TYPE']])) {
                        $typoScript .= (self::$lineBreakAfterCurlyBracketClose ? : self::$lineBreakBeforeCurlyBracketOpen) . $indentation . self::$objectPath . $key . ' = ' . $value[self::TYPO_SCRIPT_KEYS['OBJECT_TYPE']] . LF;
                        unset($value[self::TYPO_SCRIPT_KEYS['OBJECT_TYPE']]);
                        $typoScript .= self::processRemainingArray($indentationLevel, $key, $value);
                    } elseif (isset($value[self::TYPO_SCRIPT_KEYS['IMPORT']])) {
                        $typoScript .= (self::$lineBreakAfterCurlyBracketClose ? : self::$lineBreakBeforeCurlyBracketOpen) . $indentation . self::$objectPath . $key . ' < ' . $value[self::TYPO_SCRIPT_KEYS['IMPORT']] . LF;
                        unset($value[self::TYPO_SCRIPT_KEYS['IMPORT']]);
                        $typoScript .= self::processRemainingArray($indentationLevel, $key, $value);
                    } elseif (1 === count($value)) {
                        self::resetLineBreaks();
                        self::$objectPath .= $key . '.';
                        $typoScript .= self::buildTypoScriptFromArray($value,
                            $indentationLevel);
                    } else {
                        $typoScript .= self::$lineBreakBeforeCurlyBracketOpen . $indentation . self::$objectPath . $key . ' {' . LF;
                        self::resetLineBreaks();
                        self::resetObjectPath();
                        $typoScript .= self::buildTypoScriptFromArray($value, $indentationLevel + 1);
                        $typoScript .= $indentation . '}' . LF;
                        self::$lineBreakAfterCurlyBracketClose = LF;
                    }
                } else {
                    $typoScript .= self::$lineBreakAfterCurlyBracketClose . $indentation . self::$objectPath . $key . ' = ' . $value . LF;
                    self::resetObjectPath();
                    self::$lineBreakAfterCurlyBracketClose = '';
                    self::$lineBreakBeforeCurlyBracketOpen = LF;
                }
            }
        }

        return $typoScript;
    }

    /**
     * @param int $indentationLevel
     *
     * @return string
     */
    private static function createIndentation(int $indentationLevel): string
    {
        $indentation = '';

        for ($i = 0; $i < $indentationLevel; $i++) {
            $indentation .= self::INDENTATION;
        }

        return $indentation;
    }

    /**
     * @param ExtensionInformationInterface $extensionInformation
     * @param string                        $component
     *
     * @return array
     */
    private static function getDefaultConstants(
        ExtensionInformationInterface $extensionInformation,
        string $component
    ): array {
        $key = 'tx_' . mb_strtolower($extensionInformation->getExtensionName());

        return [
            $component => [
                $key => [
                    'persistence' => [
                        'storagePid' => [
                            self::TYPO_SCRIPT_KEYS['COMMENT'] => ['cat=plugin.' . $key . '//a; type=int+; label=Default storage PID'],
                            '',
                        ],
                    ],
                    'view'        => [
                        'layoutRootPath'   => [
                            self::TYPO_SCRIPT_KEYS['COMMENT'] => 'cat=plugin.' . $key . '/file; type=string; label=Path to template layouts (FE)',
                            'EXT:' . $extensionInformation->getExtensionKey() . '/Resources/Private/Layouts/',
                        ],
                        'partialRootPath'  => [
                            self::TYPO_SCRIPT_KEYS['COMMENT'] => 'cat=plugin.' . $key . '/file; type=string; label=Path to template partials (FE)',
                            'EXT:' . $extensionInformation->getExtensionKey() . '/Resources/Private/Partials/',
                        ],
                        'templateRootPath' => [
                            self::TYPO_SCRIPT_KEYS['COMMENT'] => 'cat=plugin.' . $key . '/file; type=string; label=Path to template root (FE)',
                            'EXT:' . $extensionInformation->getExtensionKey() . '/Resources/Private/Templates/',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param ExtensionInformationInterface $extensionInformation
     * @param string                        $component
     *
     * @return array
     */
    private static function getDefaultSetup(
        ExtensionInformationInterface $extensionInformation,
        string $component
    ): array {
        $key = 'tx_' . mb_strtolower($extensionInformation->getExtensionName());

        return [
            $component => [
                $key => [
                    'persistence' => [
                        'storagePid' => self::getPreparedTypoScriptConstant('plugin.' . $key . '.persistence.storagePid'),
                    ],
                    'view'        => [
                        'layoutRootPaths'   => [
                            self::getPreparedTypoScriptConstant('plugin.' . $key . '.view.layoutRootPaths'),
                        ],
                        'partialRootPaths'  => [
                            self::getPreparedTypoScriptConstant('plugin.' . $key . '.view.partialRootPaths'),
                        ],
                        'templateRootPaths' => [
                            self::getPreparedTypoScriptConstant('plugin.' . $key . '.view.templateRootPaths'),
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int        $indentationLevel
     * @param int|string $key
     * @param array      $value
     *
     * @return string
     */
    private static function processRemainingArray(int $indentationLevel, $key, array $value): string
    {
        $typoScript = '';

        if (!empty($value)) {
            self::resetLineBreaks();
            $typoScript .= self::createIndentation($indentationLevel) . self::$objectPath . $key . ' {' . LF;
            self::resetObjectPath();
            $typoScript .= self::buildTypoScriptFromArray($value, $indentationLevel + 1);
            $typoScript .= self::createIndentation($indentationLevel) . '}' . LF;
            self::$lineBreakAfterCurlyBracketClose = LF;
        }

        return $typoScript;
    }

    private static function resetLineBreaks(): void
    {
        self::$lineBreakAfterCurlyBracketClose = '';
        self::$lineBreakBeforeCurlyBracketOpen = '';
    }

    private static function resetObjectPath(): void
    {
        self::$objectPath = '';
    }
}
