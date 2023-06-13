<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Utility\Database;

/**
 * Class DefinitionUtility
 *
 * @package PSB\PsbFoundation\Utility\Database
 */
class DefinitionUtility
{
    /**
     * @param int  $default
     * @param bool $unsigned
     *
     * @return string
     */
    public static function bigint(int $default = 0, bool $unsigned = false): string
    {
        return self::integerType('bigint', $default, $unsigned);
    }

    /**
     * @param int    $length
     * @param string $default
     *
     * @return string
     */
    public static function char(int $length, string $default = ''): string
    {
        return 'char(' . $length . ') DEFAULT \'' . $default . '\'';
    }

    /**
     * @param int   $precision
     * @param int   $scale
     * @param float $default
     *
     * @return string
     */
    public static function decimal(int $precision, int $scale, float $default = 0): string
    {
        return 'decimal(' . $precision . ',' . $scale . ') DEFAULT ' . $default;
    }

    /**
     * @param float $default
     *
     * @return string
     */
    public static function double(float $default = 0): string
    {
        return 'double DEFAULT ' . $default;
    }

    /**
     * @param float $default
     *
     * @return string
     */
    public static function float(float $default = 0): string
    {
        return 'float DEFAULT ' . $default;
    }

    /**
     * @param int  $default
     * @param bool $unsigned
     *
     * @return string
     */
    public static function int(int $default = 0, bool $unsigned = false): string
    {
        return self::integerType('int', $default, $unsigned);
    }

    /**
     * @param int  $default
     * @param bool $unsigned
     *
     * @return string
     */
    public static function smallint(int $default = 0, bool $unsigned = false): string
    {
        return self::integerType('smallint', $default, $unsigned);
    }

    /**
     * @return string
     */
    public static function text(): string
    {
        return 'text';
    }

    /**
     * @param int  $default
     * @param bool $unsigned
     *
     * @return string
     */
    public static function tinyint(int $default = 0, bool $unsigned = false): string
    {
        return self::integerType('tinyint', $default, $unsigned);
    }

    /**
     * @param int    $length
     * @param string $default
     *
     * @return string
     */
    public static function varchar(int $length, string $default = ''): string
    {
        return 'varchar(' . $length . ') DEFAULT \'' . $default . '\'';
    }

    /**
     * @param string $type
     * @param int    $default
     * @param bool   $unsigned
     *
     * @return string
     */
    private static function integerType(string $type, int $default = 0, bool $unsigned = false): string
    {
        return $type . ($unsigned ? ' unsigned' : '') . ' DEFAULT ' . $default;
    }
}
