<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Utility\TypoScript;

use Generator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class TypoScriptUtilityTest
 *
 * @package PSB\PsbFoundation\Utility\TypoScript
 */
class TypoScriptUtilityTest extends UnitTestCase
{
    /**
     * @test
     * @dataProvider convertArrayToTypoScriptDataProvider
     *
     * @param array  $array
     * @param string $expectedResult
     *
     * @return void
     */
    public function convertArrayToTypoScript(array $array, string $expectedResult): void
    {
        self::assertEquals(
            $expectedResult,
            TypoScriptUtility::convertArrayToTypoScript($array)
        );
    }

    /**
     * @return Generator
     */
    public function convertArrayToTypoScriptDataProvider(): Generator
    {
        $path = GeneralUtility::getFileAbsFileName('EXT:psb_foundation/Tests/Unit/Utility/TypoScript/');

        yield 'empty array' => [
            [],
            '',
        ];
        yield 'register typeNum' => [
            [
                TypoScriptUtility::TYPO_SCRIPT_KEYS['CONDITION'] => 'request.getQueryParams()[\'type\'] == ' . 1589385441,
                'ajax_psb_foundation_typoscriptutility_test'  => [
                    TypoScriptUtility::TYPO_SCRIPT_KEYS['OBJECT_TYPE'] => 'PAGE',
                    10                                                 => [
                        '_objectType'                 => 'USER_INT',
                        'action'                      => 'test',
                        'controller'                  => 'TypoScriptUtility',
                        'extensionName'               => 'PsbFoundation',
                        'pluginName'                  => 'TypoScriptUtilityTest',
                        'switchableControllerActions' => [
                            'TypoScriptUtility' => [
                                1 => 'test',
                            ],
                        ],
                        'userFunc'                    => 'TYPO3\CMS\Extbase\Core\Bootstrap->run',
                        'vendorName'                  => 'PSB',
                    ],
                    'config'                                           => [
                        'additionalHeaders'    => [
                            10 => [
                                'header' => 'Content-type: text/html',
                            ],
                        ],
                        'admPanel'             => true,
                        'debug'                => true,
                        'disableAllHeaderCode' => true,
                    ],
                    'typeNum'                                          => 1589385441,
                ],
            ],
            file_get_contents($path . 'TypoScriptExample.typoScript'),
        ];
    }
}
