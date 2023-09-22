<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Service;

use InvalidArgumentException;
use PSB\PsbFoundation\Data\ExtensionInformationInterface;
use PSB\PsbFoundation\Exceptions\ImplementationException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use function count;
use function in_array;
use function is_array;

/**
 * Class ExtensionInformationService
 *
 * @package PSB\PsbFoundation\Service
 */
class ExtensionInformationService
{
    /**
     * @var ExtensionInformationInterface[]
     */
    protected array $extensionInformationInstances = [];

    /**
     * @param ExtensionConfiguration $extensionConfiguration
     * @param PackageManager         $packageManager
     */
    public function __construct(
        protected readonly ExtensionConfiguration $extensionConfiguration,
        protected readonly PackageManager         $packageManager,
    ) {
    }

    /**
     * @param string $className
     *
     * @return array
     */
    public function extractExtensionInformationFromClassName(string $className): array
    {
        $classNameParts = GeneralUtility::trimExplode('\\', $className, true);

        if (2 > count($classNameParts)) {
            throw new InvalidArgumentException(
                __CLASS__ . ': ' . $className . ' is not a full qualified (namespaced) class name!', 1547120513
            );
        }

        return [
            'extensionKey' => GeneralUtility::camelCaseToLowerCaseUnderscored($classNameParts[1]),
            'extensionName' => $classNameParts[1],
            'vendorName' => $classNameParts[0],
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
                if (str_starts_with($line, 'namespace ')) {
                    $namespace = rtrim(GeneralUtility::trimExplode(' ', $line)[1], ';');
                    $vendorName = explode('\\', $namespace)[0];
                    break;
                }
            }
        }

        return $vendorName;
    }

    /**
     * @return ExtensionInformationInterface[]
     * @throws ImplementationException
     */
    public function getAllExtensionInformation(): array
    {
        if (empty($this->extensionInformationInstances)) {
            $this->register();
        }

        return $this->extensionInformationInstances;
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
        string                        $path = '',
    ): mixed {
        $path = str_replace('.', '/', $path);
        $extensionConfiguration = $this->extensionConfiguration->get($extensionInformation->getExtensionKey(), $path);

        if (is_array($extensionConfiguration)) {
            return GeneralUtility::makeInstance(TypoScriptService::class)
                ->convertTypoScriptArrayToPlainArray($extensionConfiguration);
        }

        return $extensionConfiguration;
    }

    /**
     * @param ExtensionInformationInterface $extensionInformation
     *
     * @return array
     */
    public function getDomainModelClassNames(ExtensionInformationInterface $extensionInformation): array
    {
        $classNames = [];

        try {
            $finder = Finder::create()
                ->files()
                ->in(
                    ExtensionManagementUtility::extPath(
                        $extensionInformation->getExtensionKey()
                    ) . 'Classes/Domain/Model'
                )
                ->name('*.php');

            /** @var SplFileInfo $fileInfo */
            foreach ($finder as $fileInfo) {
                $classNameComponents = array_merge([
                    $extensionInformation->getVendorName(),
                    $extensionInformation->getExtensionName(),
                    'Domain\Model',
                ], explode('/', substr($fileInfo->getRelativePathname(), 0, -4)));

                $fullQualifiedClassName = implode('\\', $classNameComponents);

                if (class_exists($fullQualifiedClassName)) {
                    $classNames[] = $fullQualifiedClassName;
                }
            }
        } catch (InvalidArgumentException) {
            // No such directory in this extension
        }

        return $classNames;
    }

    /**
     * @param string $extensionKey
     *
     * @return ExtensionInformationInterface
     * @throws ImplementationException
     */
    public function getExtensionInformation(string $extensionKey): ExtensionInformationInterface
    {
        $instances = $this->getAllExtensionInformation();

        return $instances[$extensionKey] ?? throw new ImplementationException(
            __CLASS__ . ': There is no ExtensionInformation registered for ' . $extensionKey . '!', 1683560687
        );
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
    private function register(): void
    {
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
                        throw new ImplementationException(
                            __CLASS__ . ': ' . $className . ' has to implement ExtensionInformationInterface!',
                            1568738348
                        );
                    }

                    $this->extensionInformationInstances[$extensionKey] = GeneralUtility::makeInstance($className);
                }
            }
        }
    }
}
