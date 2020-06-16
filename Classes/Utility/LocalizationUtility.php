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
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use PSB\PsbFoundation\Data\ExtensionInformation;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use function array_slice;

/**
 * Class LocalizationUtility
 *
 * @package PSB\PsbFoundation\Utility
 */
class LocalizationUtility extends \TYPO3\CMS\Extbase\Utility\LocalizationUtility
{
    private const MISSING_TRANSLATIONS_TABLE = 'tx_psbfoundation_missing_translations';

    /**
     * @param string $key
     * @param bool   $keyExists
     *
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function logMissingTranslations(string $key, bool $keyExists): void
    {
        $extensionInformation = ObjectUtility::get(ExtensionInformation::class);

        if ((bool)ExtensionInformationUtility::getConfiguration($extensionInformation,
            'debug.logMissingTranslations')) {
            $connection = ObjectUtility::get(ConnectionPool::class)
                ->getConnectionForTable(self::MISSING_TRANSLATIONS_TABLE);

            // Avoid duplicates without using a select query as check for existing entries
            $connection->delete(self::MISSING_TRANSLATIONS_TABLE, [
                'locallang_key' => $key,
            ]);

            if (false === $keyExists) {
                $connection->insert(self::MISSING_TRANSLATIONS_TABLE, [
                    'locallang_key' => $key,
                ]);
            }
        }
    }

    /**
     * Returns the localized label of the LOCAL_LANG key, $key.
     *
     * @param string      $key                     The key from the LOCAL_LANG array for which to return the value.
     * @param string|null $extensionName           The name of the extension
     * @param array       $arguments               The arguments of the extension, being passed over to vsprintf
     * @param string      $languageKey             The language key or null for using the current language from the
     *                                             system
     * @param string[]    $alternativeLanguageKeys The alternative language keys if no translation was found. If null
     *                                             and we are in the frontend, then the language_alt from TypoScript
     *                                             setup will be used
     *
     * @return string|null The value from LOCAL_LANG or null if no translation was found.
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @see \TYPO3\CMS\Extbase\Utility\LocalizationUtility
     */
    public static function translate(
        $key,
        ?string $extensionName = null,
        array $arguments = null,
        string $languageKey = null,
        array $alternativeLanguageKeys = null
    ): ?string {
        $translation = parent::translate($key, $extensionName, $arguments, $languageKey, $alternativeLanguageKeys);
        self::logMissingTranslations($key, $translation ? true : false);

        return $translation;
    }

    /**
     * @param string      $key
     * @param string|null $extension
     * @param string      $newLineMarker If set, user defined new lines are created while plain line breaks will still
     *                                   be removed
     *
     * @return string
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function translateConcatenatingNewLines(
        string $key,
        string $extension = null,
        string $newLineMarker = '||'
    ): string {
        $translation = self::translate($key, $extension);
        $translation = preg_replace('/\s+/', ' ', $translation);
        if ('' !== $newLineMarker) {
            $translation = str_replace($newLineMarker, "\n", $translation);
        }

        return $translation;
    }

    /**
     * @param string      $key
     * @param string|null $extension
     *
     * @return string
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function translatePreservingNewLines(string $key, string $extension = null): string
    {
        $translation = self::translate($key, $extension);

        // split string by linebreaks and remove surrounding whitespaces for each line
        $lines = array_map('trim', explode(LF, $translation));

        // remove first and/or last element if they are empty
        if ('' === $lines[0]) {
            array_shift($lines);
        }

        if ('' === array_values(array_slice($lines, -1))[0]) {
            array_pop($lines);
        }

        return implode("\n", $lines);
    }

    /**
     * This method can be used to check if a given language key is implemented even if TYPO3's LocalizationFactory isn't
     * initialized yet.
     *
     * @param string $key
     *
     * @return bool
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function translationExists(string $key): bool
    {
        $keyParts = explode(':', $key);

        if ('LLL' === $keyParts[0]) {
            unset($keyParts[0]);
        } else {
            // @TODO: generate warning
        }

        $id = array_pop($keyParts);
        $languageFilePath = implode(':', $keyParts);
        $languageFilePath = GeneralUtility::getFileAbsFileName($languageFilePath);

        if (file_exists($languageFilePath)) {
            $xmlData = XmlUtility::convertXmlToArray(file_get_contents($languageFilePath));

            if (\TYPO3\CMS\Core\Utility\ArrayUtility::isAssociative($xmlData['xliff']['file']['body']['trans-unit'])) {
                // If file contains only one label, an additional array level has to be added for the following foreach.
                $xmlData['xliff']['file']['body']['trans-unit'] = [$xmlData['xliff']['file']['body']['trans-unit']];
            }

            foreach ($xmlData['xliff']['file']['body']['trans-unit'] as $transUnit) {
                if (isset($transUnit[XmlUtility::SPECIAL_KEYS['ATTRIBUTES']])) {
                    $transUnitTagAttributes = $transUnit[XmlUtility::SPECIAL_KEYS['ATTRIBUTES']];

                    if ($id === $transUnitTagAttributes['id']) {
                        self::logMissingTranslations($key, true);

                        return true;
                    }
                }
            }
        }

        self::logMissingTranslations($key, false);

        return false;
    }
}
