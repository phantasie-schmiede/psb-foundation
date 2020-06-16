<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019-2020 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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

use Doctrine\DBAL\Exception\TableNotFoundException;
use InvalidArgumentException;
use PSB\PsbFoundation\Data\ExtensionInformationInterface;
use PSB\PsbFoundation\Exceptions\AnnotationException;
use PSB\PsbFoundation\Exceptions\ImplementationException;
use PSB\PsbFoundation\Service\DocComment\Annotations\TcaMapping;
use PSB\PsbFoundation\Service\DocComment\DocCommentParserService;
use ReflectionException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException;

/**
 * Class ExtensionInformationUtility
 *
 * @package PSB\PsbFoundation\Utility
 */
class ExtensionInformationUtility
{
    private const EXTENSION_INFORMATION_MAPPING_TABLE = 'tx_psbfoundation_extension_information_mapping';

    /**
     * @param ExtensionInformationInterface $extensionInformation
     * @param string                        $path
     *
     * @return mixed
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function getConfiguration(
        ExtensionInformationInterface $extensionInformation,
        string $path = ''
    ) {
        $path = str_replace('.', '/', $path);
        $extensionConfiguration = ObjectUtility::get(ExtensionConfiguration::class)
            ->get($extensionInformation->getExtensionKey(), $path);

        if (is_array($extensionConfiguration)) {
            return ObjectUtility::get(TypoScriptService::class)
                ->convertTypoScriptArrayToPlainArray($extensionConfiguration);
        }

        return $extensionConfiguration;
    }

    /**
     * @return ExtensionInformationInterface[]
     * @throws Exception
     */
    public static function getExtensionInformation(): array
    {
        $extensionInformation = self::getRegisteredClassInformation();
        $extensionInformationInstances = [];

        foreach ($extensionInformation as $information) {
            if (!ExtensionManagementUtility::isLoaded($information['extension_key'])) {
                self::deregister($information['extension_key']);
                continue;
            }

            /** @var ExtensionInformationInterface $extensionInformationClass */
            $extensionInformationClass = GeneralUtility::makeInstance($information['class_name']);
            $extensionInformationInstances[$extensionInformationClass->getExtensionKey()] = $extensionInformationClass;
        }

        return $extensionInformationInstances;
    }

    /**
     * @param string $extensionKey
     *
     * @return string
     */
    public static function getLanguageFilePath(string $extensionKey): string
    {
        return self::getResourcePath($extensionKey) . 'Private/Language/';
    }

    /**
     * @return array
     */
    public static function getRegisteredClassInformation(): array
    {
        try {
            return GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable(self::EXTENSION_INFORMATION_MAPPING_TABLE)
                ->select(['class_name', 'extension_key'], self::EXTENSION_INFORMATION_MAPPING_TABLE)
                ->fetchAll();
        } catch (TableNotFoundException $tableNotFoundException) {
            return [];
        }
    }

    /**
     * @param string $extensionKey
     *
     * @return string
     */
    public static function getResourcePath(string $extensionKey): string
    {
        $subDirectoryPath = '/' . $extensionKey . '/Resources/';
        $resourcePath = Environment::getExtensionsPath() . $subDirectoryPath;

        if (is_dir($resourcePath)) {
            return $resourcePath;
        }

        return Environment::getFrameworkBasePath() . $subDirectoryPath;
    }

    /**
     * @param string $className
     *
     * @return string
     * @throws AnnotationException
     * @throws Exception
     * @throws InvalidArgumentForHashGenerationException
     * @throws ReflectionException
     */
    public static function convertClassNameToTableName(string $className): string
    {
        $docCommentParserService = ObjectUtility::get(DocCommentParserService::class);
        $docComment = $docCommentParserService->parsePhpDocComment($className);

        if (isset($docComment[TcaMapping::class])) {
            /** @var TcaMapping $tcaMapping */
            $tcaMapping = $docComment[TcaMapping::class];

            return $tcaMapping->getTable();
        }

        $classNameParts = GeneralUtility::trimExplode('\\', $className, true);

        // overwrite vendor name with extension prefix
        $classNameParts[0] = 'tx';

        return strtolower(implode('_', $classNameParts));
    }

    /**
     * @param string $className
     *
     * @return string The controller name (without the 'Controller'-part at the end) or respectively the name of the
     *                related domain model
     */
    public static function convertControllerClassToBaseName(string $className): string
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
     * @param string      $propertyName
     * @param string|null $className
     *
     * @return string
     * @throws AnnotationException
     * @throws Exception
     * @throws InvalidArgumentForHashGenerationException
     * @throws ReflectionException
     */
    public static function convertPropertyNameToColumnName(string $propertyName, string $className = null): string
    {
        if (null !== $className) {
            $docCommentParserService = ObjectUtility::get(DocCommentParserService::class);
            $docComment = $docCommentParserService->parsePhpDocComment($className, $propertyName);

            if (isset($docComment[TcaMapping::class])) {
                /** @var TcaMapping $tcaMapping */
                $tcaMapping = $docComment[TcaMapping::class];

                return $tcaMapping->getColumn();
            }
        }

        return GeneralUtility::camelCaseToLowerCaseUnderscored($propertyName);
    }

    /**
     * @param string $extensionKey
     *
     * @throws Exception
     */
    public static function deregister(string $extensionKey): void
    {
        ObjectUtility::get(ConnectionPool::class)
            ->getConnectionForTable(self::EXTENSION_INFORMATION_MAPPING_TABLE)
            ->delete(self::EXTENSION_INFORMATION_MAPPING_TABLE, ['extension_key' => $extensionKey]);
    }

    /**
     * @param string $className
     *
     * @return array
     */
    public static function extractExtensionInformationFromClassName(string $className): array
    {
        $classNameParts = GeneralUtility::trimExplode('\\', $className, true);

        if (2 > count($classNameParts)) {
            throw new InvalidArgumentException(__CLASS__ . ': ' . $className . ' is not a full qualified (namespaced) class name!',
                1547120513);
        }

        return [
            'extensionKey'  => GeneralUtility::camelCaseToLowerCaseUnderscored($classNameParts[1]),
            'extensionName' => $classNameParts[1],
            'vendorName'    => $classNameParts[0],
        ];
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
                if (StringUtility::beginsWith($line, 'namespace ')) {
                    $namespace = rtrim(GeneralUtility::trimExplode(' ', $line)[1], ';');
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
     * @throws Exception
     * @throws ImplementationException
     */
    public static function register(
        string $className,
        string $extensionKey
    ): void {
        self::validateExtensionInformationClass($className);
        self::validateExtensionKey($extensionKey);

        ObjectUtility::get(ConnectionPool::class)
            ->getConnectionForTable(self::EXTENSION_INFORMATION_MAPPING_TABLE)
            ->insert(self::EXTENSION_INFORMATION_MAPPING_TABLE,
                [
                    'class_name'    => $className,
                    'extension_key' => $extensionKey,
                ]
            );
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
