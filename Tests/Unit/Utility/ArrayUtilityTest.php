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
     * @TODO: Why is this needed? This test should not create Singleton instances!
     *
     * @var bool
     */
    protected $resetSingletonInstances = true;

    /**
     * @test
     * @dataProvider countRecursiveDataProvider
     *
     * @param array $array
     * @param int   $expectedResult
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
    public function countRecursiveDataProvider(): Generator
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
                    'value'
                ],
            ],
            5,
        ];
    }
}
