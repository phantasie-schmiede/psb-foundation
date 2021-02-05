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

use JsonException;
use PSB\PsbFoundation\Data\ExtensionInformation;
use PSB\PsbFoundation\Utility\Xml\XmlUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use function array_slice;

/**
 * Class LocalizationService
 *
 * @package PSB\PsbFoundation\Service
 */
class LocalizationService
{
    private const MISSING_TRANSLATIONS_TABLE = 'tx_psbfoundation_missing_translations';

    /**
     * @param string $key
     * @param bool   $keyExists
     *
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function logMissingTranslations(string $key, bool $keyExists): void
    {
        if ((bool)GeneralUtility::makeInstance(ExtensionInformationService::class)
            ->getConfiguration(GeneralUtility::makeInstance(ExtensionInformation::class),
                'debug.logMissingTranslations')) {
            $connection = GeneralUtility::makeInstance(ConnectionPool::class)
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
     * @param array|null  $arguments               The arguments of the extension, being passed over to vsprintf
     * @param string|null $languageKey             The language key or null for using the current language from the
     *                                             system
     * @param string[]    $alternativeLanguageKeys The alternative language keys if no translation was found. If null
     *                                             and we are in the frontend, then the language_alt from TypoScript
     *                                             setup will be used
     *
     * @return string|null The value from LOCAL_LANG or null if no translation was found.
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @see \TYPO3\CMS\Extbase\Utility\LocalizationUtility
     */
    public function translate(
        $key,
        string $extensionName = null,
        array $arguments = null,
        string $languageKey = null,
        array $alternativeLanguageKeys = null
    ): ?string {
        $translation = LocalizationUtility::translate($key, $extensionName, $arguments, $languageKey,
            $alternativeLanguageKeys);
        $this->logMissingTranslations($key, $translation ? true : false);

        return $translation;
    }

    /**
     * @param string      $key
     * @param string|null $extension
     * @param string      $newLineMarker If set, user defined new lines are created while plain line breaks will still
     *                                   be removed
     *
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function translateConcatenatingNewLines(
        string $key,
        string $extension = null,
        string $newLineMarker = '||'
    ): string {
        $translation = $this->translate($key, $extension);
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
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function translatePreservingNewLines(string $key, string $extension = null): string
    {
        $translation = $this->translate($key, $extension);

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
     * @throws JsonException
     */
    public function translationExists(string $key): bool
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
            $xmlData = XmlUtility::convertFromXml(file_get_contents($languageFilePath));

            if (isset($xmlData['xliff']['file']['body']['trans-unit'])) {
                if (ArrayUtility::isAssociative($xmlData['xliff']['file']['body']['trans-unit'])) {
                    // If file contains only one label, an additional array level has to be added for the following foreach.
                    $xmlData['xliff']['file']['body']['trans-unit'] = [$xmlData['xliff']['file']['body']['trans-unit']];
                }

                foreach ($xmlData['xliff']['file']['body']['trans-unit'] as $transUnit) {
                    if (isset($transUnit[XmlUtility::SPECIAL_KEYS['ATTRIBUTES']])) {
                        $transUnitTagAttributes = $transUnit[XmlUtility::SPECIAL_KEYS['ATTRIBUTES']];

                        if ($id === $transUnitTagAttributes['id']) {
                            $this->logMissingTranslations($key, true);

                            return true;
                        }
                    }
                }
            }
        }

        $this->logMissingTranslations($key, false);

        return false;
    }
}
