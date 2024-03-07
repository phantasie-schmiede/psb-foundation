<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Controller\Backend;

use Doctrine\DBAL\Exception;
use JsonException;
use PSB\PsbFoundation\Attribute\ModuleAction;
use PSB\PsbFoundation\Exceptions\ImplementationException;
use PSB\PsbFoundation\Service\ExtensionInformationService;
use PSB\PsbFoundation\Utility\Configuration\FilePathUtility;
use PSB\PsbFoundation\Utility\FileUtility;
use PSB\PsbFoundation\Utility\LocalizationUtility;
use PSB\PsbFoundation\Utility\StringUtility;
use PSB\PsbFoundation\Utility\Xml\XmlUtility;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use TYPO3\CMS\Backend\Attribute\Controller;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

/**
 * Class AbstractModuleController
 *
 * @package PSB\PsbFoundation\Controller\Backend
 */
#[Controller]
class AnalyzeLocalLangController extends AbstractModuleController
{
    public function __construct(
        protected readonly ExtensionInformationService $extensionInformationService,
        ModuleTemplateFactory                          $moduleTemplateFactory,
    ) {
        parent::__construct($moduleTemplateFactory);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws ImplementationException
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    #[ModuleAction(default: true)]
    public function overviewAction(): ResponseInterface
    {
        $this->moduleTemplate->assignMultiple([
            'labelAccessLogData'   => $this->fetchLabelAccessLogData(),
            'missingLabelsLogData' => $this->fetchMissingLabelsLogData(),
        ]);

        return $this->htmlResponse();
    }

    /**
     * Returns an array with all defined identifiers as keys (format: "LLL:EXT:...").
     *
     * @throws ContainerExceptionInterface
     * @throws ImplementationException
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    private function collectAllLanguageLabels(): array
    {
        $allExtensionInformation = $this->extensionInformationService->getAllExtensionInformation();
        $languageLabels = [];

        foreach ($allExtensionInformation as $extensionInformation) {
            $languageDirectory = FilePathUtility::getResourcePath($extensionInformation) . 'Private/Language/';
            $realPath = GeneralUtility::getFileAbsFileName($languageDirectory);

            if (!is_dir($realPath)) {
                continue;
            }

            $finder = Finder::create()
                ->files()
                ->in($realPath)
                ->name('*' . FilePathUtility::LANGUAGE_FILE_EXTENSION);

            /** @var SplFileInfo $fileInfo */
            foreach ($finder as $fileInfo) {
                $fileIdentifier = FilePathUtility::LANGUAGE_LABEL_PREFIX . $languageDirectory . $fileInfo->getRelativePathname(
                    );

                $xmlData = XmlUtility::convertFromXml(file_get_contents($fileInfo->getRealPath()));

                foreach ($xmlData['xliff']['file']['body']['trans-unit'] as $transUnit) {
                    if (isset($transUnit[XmlUtility::SPECIAL_ARRAY_KEYS['ATTRIBUTES']]['id'])) {
                        $languageLabels[$fileIdentifier . ':' . $transUnit[XmlUtility::SPECIAL_ARRAY_KEYS['ATTRIBUTES']]['id']] = null;
                    }
                }
            }
        }

        return $languageLabels;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ImplementationException
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    private function fetchLabelAccessLogData(): array
    {
        $languageLabels = $this->collectAllLanguageLabels();
        $logFile = FilePathUtility::getLanguageLabelLogFilesPath() . LocalizationUtility::LOG_FILES['ACCESS'];

        if (!FileUtility::fileExists($logFile)) {
            return $languageLabels;
        }

        $logData = StringUtility::explodeByLineBreaks(
            file_get_contents($logFile)
        );

        foreach ($logData as $logRecord) {
            if (empty($logRecord)) {
                continue;
            }

            [
                $timestamp,
                $key,
            ] = json_decode($logRecord, false, 512, JSON_THROW_ON_ERROR);

            if (isset($languageLabels[$key]['hitCount'])) {
                $languageLabels[$key]['hitCount']++;
                $languageLabels[$key]['lastHit'] = $timestamp;
            } else {
                $languageLabels[$key] = [
                    'firstHit' => $timestamp,
                    'hitCount' => 1,
                    'lastHit'  => $timestamp,
                ];
            }
        }

        uasort($languageLabels, static function($a, $b) {
            return ($a['hitCount'] ?? 0) > ($b['hitCount'] ?? 0);
        });

        return $languageLabels;
    }

    /**
     * @throws Exception
     * @throws JsonException
     */
    private function fetchMissingLabelsLogData(): array
    {
        LocalizationUtility::checkPostponedLogEntries();
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(LocalizationUtility::MISSING_LANGUAGE_LABELS_TABLE);

        $logData = $queryBuilder->select('*')
            ->from(LocalizationUtility::MISSING_LANGUAGE_LABELS_TABLE)
            ->executeQuery()
            ->fetchFirstColumn();
        sort($logData);

        return $logData;
    }
}
