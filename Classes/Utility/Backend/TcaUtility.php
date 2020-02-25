<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Utility\Backend;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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

use Exception;
use InvalidArgumentException;
use PSB\PsbFoundation\Service\Configuration\TcaService;
use PSB\PsbFoundation\Traits\StaticInjectionTrait;
use PSB\PsbFoundation\Utility\ExtensionInformationUtility;
use PSB\PsbFoundation\Utility\StringUtility;
use ReflectionException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class TcaUtility
 * @package PSB\PsbFoundation\Utility\Backend
 */
class TcaUtility
{
    use StaticInjectionTrait;

    /**
     * This function will be executed when the core builds the TCA, but as it does not return an array there will be no
     * entry for the required file. Instead this function expands the TCA on its own by scanning through the domain
     * models of all registered extensions (extensions which provide an ExtensionInformation class, see
     * \PSB\PsbFoundation\Data\AbstractExtensionInformation).
     * Transient domain models (those without a corresponding table in the database) will be skipped.
     *
     * @param bool $overrideMode If set to false, the configuration of all original domain models (not extending other
     *                           domain models) is added to the TCA.
     *                           If set to true, the configuration of all extending domain models is added to the TCA.
     *                           (They have to be properly annotated, see
     *                           \PSB\PsbFoundation\Service\DocComment\ValueParsers\TcaMappingParser.)
     *
     * @throws NoSuchCacheException
     * @throws ReflectionException
     * @throws Exception
     */
    public static function buildTca(bool $overrideMode): void
    {
        $allExtensionInformation = ExtensionInformationUtility::getExtensionInformation();

        foreach ($allExtensionInformation as $extensionInformation) {
            try {
                $finder = Finder::create()
                    ->files()
                    ->in(ExtensionManagementUtility::extPath($extensionInformation->getExtensionKey()) . 'Classes/Domain/Model')
                    ->name('*.php');
            } catch (InvalidArgumentException $e) {
                // No such directory in this extension
                continue;
            }

            /** @var SplFileInfo $fileInfo */
            foreach ($finder as $fileInfo) {
                $classNameComponents = array_merge(
                    [
                        $extensionInformation->getVendorName(),
                        $extensionInformation->getExtensionName(),
                        'Domain\Model',
                    ],
                    explode('/', substr($fileInfo->getRelativePathname(), 0, -4))
                );

                $fullQualifiedClassName = implode('\\', $classNameComponents);
                $tableName = ExtensionInformationUtility::convertClassNameToTableName($fullQualifiedClassName);

                if (true === $overrideMode && StringUtility::startsWith($tableName,
                        'tx_' . mb_strtolower($extensionInformation->getExtensionName()))) {
                    // This class is not extending another domain model.
                    continue;
                }

                if (false === $overrideMode && !StringUtility::startsWith($tableName,
                        'tx_' . mb_strtolower($extensionInformation->getExtensionName()))) {
                    // Not a table of the current extension, thus skipped
                    continue;
                }

                try {
                    self::get(ConnectionPool::class)
                        ->getConnectionForTable($tableName)
                        ->getSchemaManager()
                        ->tablesExist([$tableName]);
                } catch (Exception $exception) {
                    // This class seems to be no persistent domain model and will be skipped as a corresponding table is missing.
                    continue;
                }

                $tcaConfiguration = self::get(TcaService::class,
                    $fullQualifiedClassName)->buildFromDocComment()->getConfiguration();

                if (is_array($tcaConfiguration)) {
                    $GLOBALS['TCA'][$tableName] = $tcaConfiguration;
                }
            }
        }
    }
}
