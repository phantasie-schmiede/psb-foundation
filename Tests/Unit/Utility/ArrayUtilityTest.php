<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Utility;

use Generator;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class ArrayUtility
 *
 * @package PSB\PsbFoundation\Utility
 */
class ArrayUtilityTest extends UnitTestCase
{
    /**
     * @test
     * @dataProvider countRecursiveDataProvider
     *
     * @param array $array
     * @param int   $expectedResult
     *
     * @return void
     */
    public function countRecursive(array $array, int $expectedResult): void
    {
        self::assertEquals(
            $expectedResult,
            ArrayUtility::countRecursive($array)
        );
    }

    /**
     * @return Generator
     */
    public static function countRecursiveDataProvider(): Generator
    {
        yield 'empty array' => [
            [],
            0,
        ];
        yield 'simple array' => [
            [
                1,
                2,
                3,
            ],
            3,
        ];
        yield 'multidimensional array' => [
            [
                [
                    'foo' => [
                        'bar'    => 1,
                        'foobar' => 2,
                    ],
                ],
                'test',
                [
                    'bar' => 3,
                    'value',
                ],
            ],
            5,
        ];
    }
}
