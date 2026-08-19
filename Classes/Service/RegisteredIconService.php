<?php

declare(strict_types=1);

/*
 * This file is part of PSBits Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSBits\Foundation\Service;

use JsonException;
use PSBits\Foundation\Utility\Configuration\FilePathUtility;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionObject;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use function is_array;
use function is_string;
use function method_exists;
use function preg_match;
use function str_replace;

class RegisteredIconService
{
    public function __construct(
        protected readonly ExtensionInformationService $extensionInformationService,
        protected readonly IconRegistry $iconRegistry,
    ) {
    }

    /**
     * @return array<string, array{provider: class-string, source: string}>
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function getIconRegistrationConfiguration(): array
    {
        $configuration = [];

        foreach ($this->extensionInformationService->getAllExtensionInformation() as $extensionInformation) {
            $extensionKey = $extensionInformation->getExtensionKey();
            $path         = GeneralUtility::getFileAbsFileName(
                FilePathUtility::EXTENSION_DIRECTORY_PREFIX . $extensionKey . '/Resources/Public/Icons'
            );

            if (!is_dir($path)) {
                continue;
            }

            $finder = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
            );

            /** @var \SplFileInfo $fileInfo */
            foreach ($finder as $fileInfo) {
                if (!$fileInfo->isFile()) {
                    continue;
                }

                $extension = mb_strtolower($fileInfo->getExtension());

                if (!in_array($extension, ['png', 'svg'], true)) {
                    continue;
                }

                $relativePath = str_replace('\\', '/', mb_substr($fileInfo->getPathname(), mb_strlen($path) + 1));
                $source       = FilePathUtility::EXTENSION_DIRECTORY_PREFIX . $extensionKey . '/Resources/Public/Icons/' . $relativePath;

                if (1 !== preg_match('/^EXT:[a-z0-9_]+\/.+\.(png|svg)$/i', $source)) {
                    continue;
                }

                $iconIdentifier = str_replace(
                    '_',
                    '-',
                    $extensionKey
                ) . '-' . str_replace(
                    '_',
                    '-',
                    GeneralUtility::camelCaseToLowerCaseUnderscored($fileInfo->getBasename('.' . $extension))
                );

                $configuration[$iconIdentifier] = [
                    'provider' => $extension === 'svg'
                        ? \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class
                        : \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
                    'source'   => $source,
                ];
            }
        }

        ksort($configuration);

        return $configuration;
    }

    /**
     * @return array<int, array{
     *     duplicateCount: int,
     *     extensionKey: string,
     *     hasDuplicateIdentifier: bool,
     *     identifier: string,
     *     provider: class-string,
     *     source: string
     * }>
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function getRegisteredIcons(): array
    {
        $icons               = [];
        $managedExtensionKeys = [];

        foreach ($this->extensionInformationService->getAllExtensionInformation() as $extensionInformation) {
            $managedExtensionKeys[$extensionInformation->getExtensionKey()] = true;
        }

        foreach ($this->getRegisteredIconConfigurations() as $iconIdentifier => $iconConfiguration) {
            if (!isset($iconConfiguration['provider'], $iconConfiguration['source'])) {
                continue;
            }

            $provider = $iconConfiguration['provider'];
            $source   = $iconConfiguration['source'];

            if (!is_string($provider) || !is_string($source)) {
                continue;
            }

            if (1 !== preg_match('/^EXT:([a-z0-9_]+)\/.+$/i', $source, $matches)) {
                continue;
            }

            $extensionKey = $matches[1];

            if (!isset($managedExtensionKeys[$extensionKey])) {
                continue;
            }

            if (isset($icons[$iconIdentifier])) {
                ++$icons[$iconIdentifier]['duplicateCount'];
                $icons[$iconIdentifier]['hasDuplicateIdentifier'] = true;

                continue;
            }

            $icons[$iconIdentifier] = [
                'duplicateCount'         => 0,
                'extensionKey'           => $extensionKey,
                'hasDuplicateIdentifier' => false,
                'identifier'             => $iconIdentifier,
                'provider'               => $provider,
                'source'                 => $source,
            ];
        }

        uasort(
            $icons,
            static fn(array $firstIcon, array $secondIcon): int => [$firstIcon['extensionKey'], $firstIcon['identifier']] <=> [$secondIcon['extensionKey'], $secondIcon['identifier']]
        );

        return array_values($icons);
    }

    /**
     * @return array<string, array{provider?: class-string, source?: string}>
     */
    private function getRegisteredIconConfigurations(): array
    {
        if (method_exists($this->iconRegistry, 'getAllRegisteredIconIdentifiers') && method_exists(
            $this->iconRegistry,
            'getIconConfigurationByIdentifier'
        )) {
            /** @var list<string> $identifiers */
            $identifiers = $this->iconRegistry->getAllRegisteredIconIdentifiers();
            $icons       = [];

            foreach ($identifiers as $identifier) {
                $icons[$identifier] = $this->iconRegistry->getIconConfigurationByIdentifier($identifier);
            }

            return $icons;
        }

        if (method_exists($this->iconRegistry, 'getAllRegisteredIconIdentifiers') && method_exists(
            $this->iconRegistry,
            'getIconConfiguration'
        )) {
            /** @var list<string> $identifiers */
            $identifiers = $this->iconRegistry->getAllRegisteredIconIdentifiers();
            $icons       = [];

            foreach ($identifiers as $identifier) {
                $icons[$identifier] = $this->iconRegistry->getIconConfiguration($identifier);
            }

            return $icons;
        }

        $reflection = new ReflectionObject($this->iconRegistry);

        if (!$reflection->hasProperty('icons')) {
            return [];
        }

        $property = $reflection->getProperty('icons');

        /** @var mixed $icons */
        $icons = $property->getValue($this->iconRegistry);

        return is_array($icons) ? $icons : [];
    }

    /**
     * @return array<string, array<int, array{
     *     duplicateCount: int,
     *     extensionKey: string,
     *     hasDuplicateIdentifier: bool,
     *     identifier: string,
     *     provider: class-string,
     *     source: string
     * }>>
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function getRegisteredIconsGroupedByExtension(): array
    {
        $groupedIcons = [];

        foreach ($this->getRegisteredIcons() as $icon) {
            $groupedIcons[$icon['extensionKey']][] = $icon;
        }

        ksort($groupedIcons);

        return $groupedIcons;
    }
}
