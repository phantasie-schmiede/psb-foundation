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

namespace PSB\PsbFoundation\Service;

use InvalidArgumentException;
use PSB\PsbFoundation\Data\ExtensionInformationInterface;
use PSB\PsbFoundation\Exceptions\ImplementationException;
use PSB\PsbFoundation\Traits\PropertyInjection\ExtensionConfigurationTrait;
use PSB\PsbFoundation\Traits\PropertyInjection\PackageManagerTrait;
use PSB\PsbFoundation\Utility\StringUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Package\Exception\UnknownPackageException;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ExtensionInformationService
 *
 * @package PSB\PsbFoundation\Service
 */
class ExtensionInformationService
{
    use ExtensionConfigurationTrait, PackageManagerTrait;

    /**
     * @var ExtensionInformationInterface[]
     */
    protected array $extensionInformationInstances = [];

    /**
     * @param string $className
     *
     * @return array
     */
    public function extractExtensionInformationFromClassName(string $className): array
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
    public function extractVendorNameFromFile(string $fileName): ?string
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
     * This function is called once and very early in ext_localconf.php. It scans all active packages and checks if
     * there is an ExtensionInformation-class. If so, an instance is created and stored for upcoming usages. The order
     * of the stored instances respects their dependencies as resolved by the PackageManager. This register is used for
     * a series of automated tasks like TCA-generation, icon registration and plugin configuration.
     *
     * @return void
     * @throws ImplementationException
     */
    public function register(): void
    {
        if (!empty($this->extensionInformationInstances)) {
            return;
        }

        $activePackages = $this->packageManager->getActivePackages();

        foreach ($activePackages as $package) {
            $extensionKey = $package->getPackageKey();
            $fileName = $package->getPackagePath() . 'Classes/Data/ExtensionInformation.php';
            $vendorName = $this->extractVendorNameFromFile($fileName);

            if (null !== $vendorName) {
                $className = implode('\\', [
                    $vendorName,
                    GeneralUtility::underscoredToUpperCamelCase($extensionKey),
                    'Data\ExtensionInformation',
                ]);

                if (class_exists($className)) {
                    if (!in_array(ExtensionInformationInterface::class, class_implements($className), true)) {
                        throw new ImplementationException(__CLASS__ . ': ' . $className . ' has to implement ExtensionInformationInterface!',
                            1568738348);
                    }

                    $this->extensionInformationInstances[$extensionKey] = GeneralUtility::makeInstance($className);
                }
            }
        }
    }

    /**
     * Additional wrapper function to access specific settings defined in ext_conf_template.txt of an extension more
     * easily.
     *
     * @param ExtensionInformationInterface $extensionInformation
     * @param string                        $path
     *
     * @return mixed
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function getConfiguration(
        ExtensionInformationInterface $extensionInformation,
        string $path = ''
    ) {
        $path = str_replace('.', '/', $path);
        $extensionConfiguration = $this->extensionConfiguration->get($extensionInformation->getExtensionKey(), $path);

        if (is_array($extensionConfiguration)) {
            return GeneralUtility::makeInstance(TypoScriptService::class)
                ->convertTypoScriptArrayToPlainArray($extensionConfiguration);
        }

        return $extensionConfiguration;
    }

    /**
     * @return ExtensionInformationInterface[]
     */
    public function getExtensionInformation(): array
    {
        return $this->extensionInformationInstances;
    }

    /**
     * @param ExtensionInformationInterface $extensionInformation
     *
     * @return string
     * @throws UnknownPackageException
     */
    public function getLanguageFilePath(ExtensionInformationInterface $extensionInformation): string
    {
        return $this->getResourcePath($extensionInformation) . 'Private/Language/';
    }

    /**
     * @param ExtensionInformationInterface $extensionInformation
     *
     * @return string
     * @throws UnknownPackageException
     */
    public function getResourcePath(ExtensionInformationInterface $extensionInformation): string
    {
        $subDirectoryPath = '/' . $extensionInformation->getExtensionKey() . '/Resources/';

        return $this->packageManager->getPackage($extensionInformation->getExtensionKey())
                ->getPackagePath() . $subDirectoryPath;
    }
}
