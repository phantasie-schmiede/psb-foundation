<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Service;

use JsonException;
use PSB\PsbFoundation\Data\ExtensionInformation;
use PSB\PsbFoundation\Utility\Configuration\FilePathUtility;
use PSB\PsbFoundation\Utility\ContextUtility;
use PSB\PsbFoundation\Utility\FileUtility;
use PSB\PsbFoundation\Utility\StringUtility;
use PSB\PsbFoundation\Utility\Xml\XmlUtility;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use function array_slice;

/**
 * Class LocalizationService
 *
 * @package PSB\PsbFoundation\Service
 */
class LocalizationService
{
    private const MISSING_LANGUAGE_LABELS_TABLE = 'tx_psbfoundation_missing_language_labels';
    private const TEMP_LOG_FILE                 = 'log/psb_foundation/postponed_language_labels.log';

    protected string $logFilePath;

    /**
     * @param ExtensionInformationService $extensionInformationService
     * @param ExtensionInformation        $extensionInformation
     */
    public function __construct(
        protected readonly ExtensionInformationService $extensionInformationService,
        protected readonly ExtensionInformation        $extensionInformation,
    ) {
        $this->logFilePath = rtrim(Environment::getVarPath(), '/') . '/' . self::TEMP_LOG_FILE;
    }

    /**
     * Returns the localized label of the LOCAL_LANG key, $key.
     *
     * @param string      $key                     The key from the LOCAL_LANG array for which to return the value.
     * @param string|null $extensionName           The name of the extension
     * @param array|null  $arguments               The arguments of the extension, being passed over to vsprintf
     * @param string|null $languageKey             The language key or null for using the current language from the
     *                                             system
     *
     * @return string|null The value from LOCAL_LANG or null if no translation was found.
     * @throws ContainerExceptionInterface
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     * @see LocalizationUtility
     */
    public function translate(
        string $key,
        string $extensionName = null,
        array  $arguments = null,
        string $languageKey = null,
    ): ?string {
        $translation = LocalizationUtility::translate($key, $extensionName, $arguments, $languageKey);
        $this->logMissingLanguageLabels($key, null !== $translation);

        return $translation;
    }

    /**
     * @param string      $key
     * @param string|null $extension
     * @param string      $newLineMarker If set, user defined new lines are created while plain line breaks will still
     *                                   be removed
     *
     * @return string
     * @throws ContainerExceptionInterface
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function translateConcatenatingNewLines(
        string $key,
        string $extension = null,
        string $newLineMarker = '||',
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
     * @throws ContainerExceptionInterface
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
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
     * @param bool   $logMissingTranslation
     *
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function translationExists(string $key, bool $logMissingTranslation = true): bool
    {
        $keyParts = explode(':', $key);

        if ('LLL' === $keyParts[0]) {
            unset($keyParts[0]);
        }

        $id = array_pop($keyParts);
        $languageFilePath = implode(':', $keyParts);
        $languageFilePath = GeneralUtility::getFileAbsFileName($languageFilePath);

        if (file_exists($languageFilePath)) {
            $xmlData = XmlUtility::convertFromXml(file_get_contents($languageFilePath));
            $transUnitArray = $xmlData['xliff']['file']['body']['trans-unit'] ?? null;

            if (null !== $transUnitArray) {
                // If file contains only one label, an additional array level has to be added for the following foreach.
                if (ArrayUtility::isAssociative($transUnitArray)) {
                    $transUnitArray = [$transUnitArray];
                }

                foreach ($transUnitArray as $transUnit) {
                    if (isset($transUnit[XmlUtility::SPECIAL_KEYS['ATTRIBUTES']])) {
                        $transUnitTagAttributes = $transUnit[XmlUtility::SPECIAL_KEYS['ATTRIBUTES']];

                        if ($id === $transUnitTagAttributes['id']) {
                            $this->logMissingLanguageLabels($key, true);

                            return true;
                        }
                    }
                }
            }
        }

        if ($logMissingTranslation) {
            $this->logMissingLanguageLabels($key, false);
        }

        return false;
    }

    /**
     * @param string $label
     *
     * @return bool
     * @throws ContainerExceptionInterface
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function validateLabel(string $label): bool
    {
        if ('' === $label) {
            return false;
        }

        if (!str_starts_with($label, FilePathUtility::LANGUAGE_LABEL_PREFIX)) {
            return true;
        }

        return $this->translationExists($label);
    }

    /**
     * @param string $key
     * @param bool   $keyExists
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    private function logMissingLanguageLabels(string $key, bool $keyExists): void
    {
        if ($this->extensionInformationService->getConfiguration($this->extensionInformation,
            'debug.logMissingLanguageLabels')) {
            if (ContextUtility::isBootProcessRunning()) {
                /*
                 * The TCA is not loaded yet. That means the ConnectionPool is not available and the logging has to be
                 * postponed.
                 */
                FileUtility::write($this->logFilePath, json_encode([$key, $keyExists], JSON_THROW_ON_ERROR) . LF, true);
            } else {
                // Check for postponed log entries.
                if (file_exists($this->logFilePath) && $logContent = file_get_contents($this->logFilePath)) {
                    $postponedEntries = StringUtility::explodeByLineBreaks($logContent);

                    foreach (array_filter($postponedEntries) as $postponedEntry) {
                        [$postponedKey, $postponedKeyExists] = json_decode($postponedEntry, false, 512,
                            JSON_THROW_ON_ERROR);
                        $this->writeLogToDatabase($postponedKey, $postponedKeyExists);
                    }

                    unlink($this->logFilePath);
                }

                $this->writeLogToDatabase($key, $keyExists);
            }
        }
    }

    /**
     * @param string $key
     * @param bool   $keyExists
     *
     * @return void
     */
    private function writeLogToDatabase(string $key, bool $keyExists): void
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable(self::MISSING_LANGUAGE_LABELS_TABLE);

        // Avoid duplicates without using a select query as check for existing entries
        $connection->delete(self::MISSING_LANGUAGE_LABELS_TABLE, [
            'locallang_key' => $key,
        ]);

        if (false === $keyExists) {
            $connection->insert(self::MISSING_LANGUAGE_LABELS_TABLE, [
                'locallang_key' => $key,
            ]);
        }
    }
}
