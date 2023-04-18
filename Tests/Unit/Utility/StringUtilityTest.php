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
use JsonException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class StringUtilityTest
 *
 * @package PSB\PsbFoundation\Utility
 */
class StringUtilityTest extends UnitTestCase
{
    public const TEST_CONSTANT       = 'test';
    public const TEST_CONSTANT_ARRAY = [
        'INDEX' => 'test',
    ];

    /**
     * @test
     * @dataProvider convertStringDataProvider
     *
     * @param string $string
     * @param        $expectedResult
     *
     * @return void
     * @throws JsonException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidConfigurationTypeException
     */
    public function convertString(string $string, $expectedResult): void
    {
        self::assertEquals(
            $expectedResult,
            StringUtility::convertString($string)
        );
    }

    /**
     * @return Generator
     */
    public static function convertStringDataProvider(): Generator
    {
        yield 'empty string' => [
            '',
            '',
        ];
        yield 'zero' => [
            '0',
            0,
        ];
        yield 'integer' => [
            '123',
            123,
        ];
        yield 'decimal with period' => [
            '0.1',
            0.1,
        ];
        yield 'decimal with comma' => [
            '0,1',
            0.1,
        ];
        yield 'leading zeros are not truncated' => [
            '0123',
            '0123',
        ];
        yield 'boolean false' => [
            'false',
            false,
        ];
        yield 'boolean true' => [
            'true',
            true,
        ];
        yield 'numeric CSV with two elements' => [
            '12, 521',
            '12, 521',
        ];
        yield 'CSV with more elements' => [
            '0, 121, abc',
            '0, 121, abc',
        ];
        yield 'JSON array' => [
            '[1,2,3]',
            [1, 2, 3],
        ];
        yield 'JSON object' => [
            '{"1":1,"2":2,"3":3}',
            [1 => 1, 2 => 2, 3 => 3],
        ];
        yield 'constant' => [
            '\PSB\PsbFoundation\Utility\StringUtilityTest::TEST_CONSTANT',
            self::TEST_CONSTANT,
        ];
        yield 'array constant with quotes' => [
            '\PSB\PsbFoundation\Utility\StringUtilityTest::TEST_CONSTANT_ARRAY[\'INDEX\']',
            self::TEST_CONSTANT_ARRAY['INDEX'],
        ];
        yield 'array constant without quotes' => [
            '\PSB\PsbFoundation\Utility\StringUtilityTest::TEST_CONSTANT_ARRAY[INDEX]',
            self::TEST_CONSTANT_ARRAY['INDEX'],
        ];
        // @TODO: test TypoScript ('TS:...')!
    }
}
