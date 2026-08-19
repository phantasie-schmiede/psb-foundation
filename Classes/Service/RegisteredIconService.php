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
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use function in_array;
use function preg_match;
use function str_replace;
use function strtolower;

class RegisteredIconService
{
    public function __construct(
        protected readonly ExtensionInformationService $extensionInformationService,
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

        foreach ($this->getRegisteredIcons() as $icon) {
            $configuration[$icon['identifier']] = [
                'provider' => $icon['provider'],
                'source'   => $icon['source'],
            ];
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
        $icons = [];

        foreach ($this->extensionInformationService->getAllExtensionInformation() as $extensionInformation) {
            $extensionKey = $extensionInformation->getExtensionKey();
            $path         = GeneralUtility::getFileAbsFileName(
                FilePathUtility::EXTENSION_DIRECTORY_PREFIX . $extensionKey . '/Resources/Public/Icons'
            );

            if (!is_dir($path)) {
                continue;
            }

            $finder = Finder::create()
                ->files()
                ->in($path)
                ->name([
                    '*.png',
                    '*.svg',
                ])
                ->sortByName();

            /** @var SplFileInfo $fileInfo */
            foreach ($finder as $fileInfo) {
                $iconIdentifier = str_replace(
                    '_',
                    '-',
                    $extensionKey
                ) . '-' . str_replace(
                    '_',
                    '-',
                    GeneralUtility::camelCaseToLowerCaseUnderscored($fileInfo->getFilenameWithoutExtension())
                );
                $source         = FilePathUtility::EXTENSION_DIRECTORY_PREFIX . $extensionKey . '/Resources/Public/Icons/' . str_replace(
                        '\\',
                        '/',
                        $fileInfo->getRelativePathname()
                    );

                if (1 !== preg_match('/^EXT:[a-z0-9_]+\/.+\.(png|svg)$/i', $source)) {
                    continue;
                }

                if (isset($icons[$iconIdentifier])) {
                    ++$icons[$iconIdentifier]['duplicateCount'];
                    $icons[$iconIdentifier]['hasDuplicateIdentifier'] = true;

                    continue;
                }

                $providerClass = strtolower($fileInfo->getExtension());

                $icons[$iconIdentifier] = [
                    'duplicateCount'          => 0,
                    'extensionKey'            => $extensionKey,
                    'hasDuplicateIdentifier'  => false,
                    'identifier'              => $iconIdentifier,
                    'provider'                => in_array($providerClass, ['svg'], true) ? SvgIconProvider::class : BitmapIconProvider::class,
                    'source'                  => $source,
                ];
            }
        }

        uasort(
            $icons,
            static fn(array $firstIcon, array $secondIcon): int => [$firstIcon['extensionKey'], $firstIcon['identifier']] <=> [$secondIcon['extensionKey'], $secondIcon['identifier']]
        );

        return array_values($icons);
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
