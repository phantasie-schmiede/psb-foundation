<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Utility\TypoScript;

use PSB\PsbFoundation\Data\ExtensionInformationInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use UnexpectedValueException;
use function count;
use function is_array;

/**
 * Class TypoScriptUtility
 *
 * @package PSB\PsbFoundation\Utility
 */
class TypoScriptUtility
{
    public const COMPONENTS = [
        'MODULE' => 'module',
        'PLUGIN' => 'plugin',
    ];

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
    private static string $lineBreakAfterCurlyBracketClose = '';

    /**
     * @var string
     */
    private static string $lineBreakBeforeCurlyBracketOpen = '';

    /**
     * @var string
     */
    private static string $objectPath = '';

    /**
     * @param array $array
     *
     * @return string
     */
    public static function convertArrayToTypoScript(array $array): string
    {
        if (Environment::getContext()->isDevelopment()) {
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
     * @param PageObjectConfiguration $pageTypeConfiguration
     *
     * @return void
     */
    public static function registerPageType(PageObjectConfiguration $pageTypeConfiguration): void
    {
        if (true === $pageTypeConfiguration->isCacheable()) {
            $internalContentType = 'USER';
        } else {
            $internalContentType = 'USER_INT';
        }

        if ('' !== $pageTypeConfiguration->getUserFunc()) {
            $contentConfiguration = array_merge([
                'userFunc' => $pageTypeConfiguration->getUserFunc(),
            ], $pageTypeConfiguration->getUserFuncParameters());
        } else {
            $contentConfiguration = [
                'extensionName'               => $pageTypeConfiguration->getExtensionName(),
                'pluginName'                  => $pageTypeConfiguration->getPluginName(),
                'userFunc'                    => 'TYPO3\CMS\Extbase\Core\Bootstrap->run',
                'vendorName'                  => $pageTypeConfiguration->getVendorName(),
            ];

            if ([] !== $pageTypeConfiguration->getSettings()) {
                $contentConfiguration['settings'] = $pageTypeConfiguration->getSettings();
            }
        }

        $contentConfiguration[self::TYPO_SCRIPT_KEYS['OBJECT_TYPE']] = $internalContentType;

        $typoScript = [
            $pageTypeConfiguration->getTypoScriptObjectName() => [
                self::TYPO_SCRIPT_KEYS['OBJECT_TYPE'] => 'PAGE',
                10                                    => $contentConfiguration,
                'config'                              => [
                    'additionalHeaders'    => [
                        10 => [
                            'header' => 'Content-type: ' . $pageTypeConfiguration->getContentType()->value,
                        ],
                    ],
                    'admPanel'             => false,
                    'debug'                => false,
                    'disableAllHeaderCode' => $pageTypeConfiguration->isDisableAllHeaderCode(),
                ],
                'typeNum'                             => $pageTypeConfiguration->getTypeNum(),
            ],
        ];

        ExtensionManagementUtility::addTypoScriptSetup(self::convertArrayToTypoScript($typoScript));
    }

    /**
     * For use in Configuration/TCA/Overrides/sys_template.php files
     *
     * @param ExtensionInformationInterface $extensionInformation
     * @param string                        $path
     * @param string                        $title
     *
     * @return void
     */
    public static function registerTypoScript(
        ExtensionInformationInterface $extensionInformation,
        string $path,
        string $title,
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
                        $typoScript .= self::buildTypoScriptFromArray($value, $indentationLevel);
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
        return str_repeat(self::INDENTATION, $indentationLevel);
    }

    /**
     * @param int        $indentationLevel
     * @param int|string $key
     * @param array      $value
     *
     * @return string
     */
    private static function processRemainingArray(int $indentationLevel, int|string $key, array $value): string
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

    /**
     * @return void
     */
    private static function resetLineBreaks(): void
    {
        self::$lineBreakAfterCurlyBracketClose = '';
        self::$lineBreakBeforeCurlyBracketOpen = '';
    }

    /**
     * @return void
     */
    private static function resetObjectPath(): void
    {
        self::$objectPath = '';
    }
}
