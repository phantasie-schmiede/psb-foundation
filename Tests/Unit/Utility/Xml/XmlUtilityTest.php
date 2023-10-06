<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Utility\Xml;

use Generator;
use JsonException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class XmlUtilityTest
 *
 * @package PSB\PsbFoundation\Utility\Xml
 */
class XmlUtilityTest extends UnitTestCase
{
    public static function convertFromAndToXmlDataProvider(): Generator
    {
        yield 'empty array' => [
            [],
            0,
        ];
    }

    public static function convertFromXmlDataProvider(): Generator
    {
        yield 'simple xml' => [
            [],
            file_get_contents(__DIR__ . '/SimpleXml.xml'),
        ];
    }

    public static function convertToXmlDataProvider(): Generator
    {
        yield 'empty array' => [
            [],
            0,
        ];
    }

    /**
     * @test
     * @dataProvider convertFromAndToXmlDataProvider
     *
     * @throws ContainerExceptionInterface
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function convertFromAndToXml(string $xml): void
    {
        $array = XmlUtility::convertFromXml($xml);
        self::assertEquals(
            $xml,
            XmlUtility::convertToXml($array)
        );
    }

    /**
     * @test
     * @dataProvider convertFromXmlDataProvider
     *
     * @throws ContainerExceptionInterface
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function convertFromXml(array $expectedResult, string $xml): void
    {
        self::assertEquals(
            $expectedResult,
            XmlUtility::convertFromXml($xml)
        );
    }

    /**
     * @test
     * @dataProvider convertToXmlDataProvider
     */
    public function convertToXml(array $array, string $expectedResult): void
    {
        self::assertEquals(
            $expectedResult,
            XmlUtility::convertToXml($array)
        );
    }
}
