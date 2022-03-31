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
        yield 'empty array' => [
            [],
            '',
        ];
    }
}
