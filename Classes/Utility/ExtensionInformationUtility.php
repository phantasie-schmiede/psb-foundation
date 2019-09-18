<?php
declare(strict_types=1);

namespace PSB\PsbFoundation\Utility;

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

use InvalidArgumentException;
use PSB\PsbFoundation\Data\ExtensionInformationInterface;
use PSB\PsbFoundation\Exceptions\ImplementationException;
use PSB\PsbFoundation\Service\DocComment\DocCommentParserService;
use PSB\PsbFoundation\Service\DocComment\ValueParsers\TcaMappingParser;
use PSB\PsbFoundation\Traits\StaticInjectionTrait;
use ReflectionException;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ExtensionInformationUtility
 * @package PSB\PsbFoundation\Utility
 */
class ExtensionInformationUtility
{
    use StaticInjectionTrait;

    private const EXTENSION_INFORMATION_MAPPING_TABLE = 'tx_psbfoundation_extension_information_mapping';

    /**
     * @TODO: check if necessary
     *
     * @param string $className
     *
     * @return string The controller name for Extbase-configurations (without the 'Controller'-part)
     */
    public static function convertClassNameToControllerName(string $className): string
    {
        $classNameParts = GeneralUtility::trimExplode('\\', $className, true);

        if (4 > count($classNameParts)) {
            throw new InvalidArgumentException(__CLASS__ . ': ' . $className . ' is not a full qualified (namespaced) class name!',
                1560233275);
        }

        $controllerNameParts = array_slice($classNameParts, 3);
        $fullControllerName = implode('\\', $controllerNameParts);

        if (!StringUtility::endsWith($fullControllerName, 'Controller')) {
            throw new InvalidArgumentException(__CLASS__ . ': ' . $className . ' is not a controller class!',
                1560233166);
        }

        return substr($fullControllerName, 0, -10);
    }

    /**
     * @param string $className
     *
     * @return string
     */
    public static function convertClassNameToExtensionKey(string $className): string
    {
        $classNameParts = GeneralUtility::trimExplode('\\', $className, true);

        if (isset($classNameParts[1])) {
            return GeneralUtility::camelCaseToLowerCaseUnderscored($classNameParts[1]);
        }

        throw new InvalidArgumentException(__CLASS__ . ': ' . $className . ' is not a full qualified (namespaced) class name!',
            1547120513);
    }

    /**
     * @param string $className
     *
     * @return string
     * @throws ReflectionException
     * @throws NoSuchCacheException
     */
    public static function convertClassNameToTableName(string $className): string
    {
        $docCommentParserService = self::get(DocCommentParserService::class);
        $docComment = $docCommentParserService->parsePhpDocComment($className);

        if (isset($docComment[TcaMappingParser::ANNOTATION_TYPE]['table'])) {
            return $docComment[TcaMappingParser::ANNOTATION_TYPE]['table'];
        }

        $classNameParts = GeneralUtility::trimExplode('\\', $className, true);
        $classNameParts[0] = 'tx';

        return strtolower(implode('_', $classNameParts));
    }

    /**
     * @param string      $propertyName
     * @param string|null $className
     *
     * @return string
     * @throws NoSuchCacheException
     * @throws ReflectionException
     */
    public static function convertPropertyNameToColumnName(string $propertyName, string $className = null): string
    {
        if (null !== $className) {
            $docCommentParserService = self::get(DocCommentParserService::class);
            $docComment = $docCommentParserService->parsePhpDocComment($className, $propertyName);
        }

        return $docComment[TcaMappingParser::ANNOTATION_TYPE]['column'] ?? GeneralUtility::camelCaseToLowerCaseUnderscored($propertyName);
    }

    /**
     * @param string $fileName
     *
     * @return string|null
     */
    public static function extractVendorNameFromFile(string $fileName): ?string
    {
        $vendorName = null;

        if (file_exists($fileName)) {
            $file = fopen($fileName, 'rb');

            while ($line = fgets($file)) {
                if (StringUtility::startsWith($line, 'namespace ')) {
                    $namespace = rtrim(GeneralUtility::trimExplode($line, ' ')[1], ';');
                    $vendorName = explode('\\', $namespace)[0];
                    break;
                }
            }
        }

        return $vendorName;
    }

    /**
     * @param string $className Full qualified class name of your extension information class (must implement
     *                          \PSB\PsbFoundation\Data\ExtensionInformationInterface, you can extend
     *                          \PSB\PsbFoundation\Data\AbstractExtensionInformation)
     * @param string $extensionKey
     *
     * @throws ImplementationException
     */
    public static function register(
        string $className,
        string $extensionKey
    ): void {
        self::validateExtensionInformationClass($className);
        self::validateExtensionKey($extensionKey);

        self::get(ConnectionPool::class)
            ->getConnectionForTable(self::EXTENSION_INFORMATION_MAPPING_TABLE)
            ->insert(self::EXTENSION_INFORMATION_MAPPING_TABLE,
                [
                    'class_name'    => $className,
                    'extension_key' => $extensionKey,
                ]
            );
    }

    public static function deregister(string $extensionKey): void
    {
        $connection = self::get(ConnectionPool::class)
            ->getConnectionForTable(self::EXTENSION_INFORMATION_MAPPING_TABLE);
        $connection->delete(self::EXTENSION_INFORMATION_MAPPING_TABLE, ['extension_key' => $extensionKey]);
    }

    /**
     * @param string $className
     *
     * @throws ImplementationException
     */
    private static function validateExtensionInformationClass(string $className): void
    {
        if (!in_array(ExtensionInformationInterface::class, class_implements($className), true)) {
            throw new ImplementationException(__CLASS__ . ': ' . $className . ' has to implement ExtensionInformationInterface!',
                1568738348);
        }
    }

    /**
     * @param string $extensionKey
     *
     * @throws ImplementationException
     */
    private static function validateExtensionKey(string $extensionKey): void
    {
        if (!ExtensionManagementUtility::isLoaded($extensionKey)) {
            throw new ImplementationException(__CLASS__ . ': The key "' . $extensionKey . '" does not match any installed extension!',
                1568738493);
        }
    }
}
