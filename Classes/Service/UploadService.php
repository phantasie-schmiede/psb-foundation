<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Service;

use PSB\PsbFoundation\Exceptions\MisconfiguredTcaException;
use PSB\PsbFoundation\Service\Configuration\TcaService;
use PSB\PsbFoundation\Utility\ArrayUtility;
use PSB\PsbFoundation\Utility\ContextUtility;
use PSB\PsbFoundation\Utility\FileUtility;
use PSB\PsbFoundation\Utility\StringUtility;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\Exception\IniSizeFileException;
use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ResourceStorageInterface;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Reflection\Exception\PropertyNotAccessibleException;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use function in_array;

/**
 * Class UploadService
 *
 * @package PSB\PsbFoundation\Service
 */
class UploadService
{
    public const DEFAULT_UPLOAD_DIRECTORY = 'user_upload';

    private array $uploadConfiguration = [];

    /**
     * @param FileReferenceService $fileReferenceService
     * @param StorageRepository    $storageRepository
     * @param TcaService           $tcaService
     */
    public function __construct(
        protected readonly FileReferenceService $fileReferenceService,
        protected readonly StorageRepository $storageRepository,
        protected readonly TcaService $tcaService,
    ) {
    }

    /**
     * Stores uploaded files of an extbase request and maps them to domain model properties. The name of the upload form
     * fields have to be identical with the properties' names!
     *
     * @param AbstractEntity $domainModel
     * @param Request        $request
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws MisconfiguredTcaException
     * @throws NotFoundExceptionInterface
     * @throws PropertyNotAccessibleException
     * @throws ReflectionException
     */
    public function fromRequest(AbstractEntity $domainModel, Request $request): void
    {
        $uploadedFilesCollection = $request->getUploadedFiles()[ContextUtility::getPluginSignatureFromRequest($request)];

        if (empty($uploadedFilesCollection)) {
            return;
        }

        // Preparation
        $domainModelReflection = new ReflectionClass($domainModel);
        $uploadedFilesCollection = array_filter($uploadedFilesCollection,
            static function ($property) use ($domainModelReflection) {
                return $domainModelReflection->hasProperty($property);
            }, ARRAY_FILTER_USE_KEY);
        $this->collectFieldConfigurations($domainModel, array_keys($uploadedFilesCollection));
        $this->validateUploadedFiles($uploadedFilesCollection);
        $this->provideTargetFolders();

        // Execution
        foreach ($uploadedFilesCollection as $property => $uploadedFiles) {
            foreach ($uploadedFiles as $uploadedFile) {
                $targetFileName = $this->buildTargetFileName($domainModel, $property, $uploadedFile);
                $file = $this->moveUploadedFileToFileStorage($property, $targetFileName, $uploadedFile);
                $this->fileReferenceService->create($domainModel, $file, $property);
            }
        }
    }

    /**
     * @param AbstractEntity $domainModel
     * @param string         $property
     * @param UploadedFile   $uploadedFile
     *
     * @return string|null
     * @throws PropertyNotAccessibleException
     */
    private function buildTargetFileName(
        AbstractEntity $domainModel,
        string $property,
        UploadedFile $uploadedFile
    ): ?string {
        $fileNameGeneratorConfiguration = $this->uploadConfiguration[$property]['fileNameGenerator'] ?? [];

        $nameParts = [];

        if ('' !== ($fileNameGeneratorConfiguration['prefix'] ?? '')) {
            $nameParts[] = $fileNameGeneratorConfiguration['prefix'];
        }

        if (!empty($fileNameGeneratorConfiguration['properties'] ?? [])) {
            foreach ($fileNameGeneratorConfiguration['properties'] as $fileNameProperty) {
                $nameParts[] = ObjectAccess::getProperty($domainModel, $fileNameProperty);
            }
        } elseif (!empty($uploadedFile->getClientFilename())) {
            $nameParts[] = StringUtility::removeSpecialChars($uploadedFile->getClientFilename());
        }

        if ('' !== ($fileNameGeneratorConfiguration['suffix'] ?? '')) {
            $nameParts[] = $fileNameGeneratorConfiguration['suffix'];
        }

        if (!empty($fileNameGeneratorConfiguration['replacements'] ?? [])) {
            array_walk($nameParts, static function (&$namePart) use ($fileNameGeneratorConfiguration) {
                $namePart = str_replace(array_keys($fileNameGeneratorConfiguration['replacements']),
                    array_values($fileNameGeneratorConfiguration['replacements']), $namePart);
            });
        }

        if (true === $fileNameGeneratorConfiguration['appendHash']) {
            $nameParts[] = hash_file('crc32', $uploadedFile->getTemporaryFileName());
        }

        if (empty($nameParts)) {
            return null;
        }

        return implode($fileNameGeneratorConfiguration['partSeparator'],
                $nameParts) . '.' . $this->getFileExtensionByMimeType($uploadedFile);
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param string       $property
     *
     * @return void
     */
    private function checkFileSize(UploadedFile $uploadedFile, string $property): void
    {
        if (isset($this->uploadConfiguration[$property]['maxSize']) && (int)$this->uploadConfiguration[$property]['maxSize'] < $uploadedFile->getSize()) {
            throw new RuntimeException('Too large!');
        }
    }

    /**
     * @param UploadedFile $uploadedFile
     *
     * @return void
     */
    private function checkForError(UploadedFile $uploadedFile): void
    {
        switch ($uploadedFile->getError()) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('No file sent.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new IniSizeFileException('Exceeded filesize limit.');
            default:
                throw new RuntimeException('Unknown errors.');
        }
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param string       $property
     *
     * @return void
     */
    private function checkMimeType(UploadedFile $uploadedFile, string $property): void
    {
        $mimeType = FileUtility::getMimeType($uploadedFile->getTemporaryFileName());

        if ($uploadedFile->getClientMediaType() !== $mimeType) {
            throw new RuntimeException(__CLASS__ . ': Transmitted and actual file type diverge!', 1678280985);
        }

        $allowedFileExtensions = $this->uploadConfiguration[$property]['allowed'] ?? null;
        $fileExtension = $this->getFileExtensionByMimeType($uploadedFile);

        if (null !== $allowedFileExtensions && !in_array($fileExtension, $allowedFileExtensions, true)) {
            throw new RuntimeException(__CLASS__ . ': File type not allowed (has to be one of: ' . implode(', ',
                    $allowedFileExtensions) . ')!', 1678280990);
        }
    }

    /**
     * @param AbstractEntity $domainModel
     * @param array          $properties
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    private function collectFieldConfigurations(AbstractEntity $domainModel, array $properties): void
    {
        $this->uploadConfiguration = [];

        foreach ($properties as $property) {
            $fieldConfiguration = $this->tcaService->getConfigurationForPropertyOfDomainModel($domainModel, $property);
            $this->uploadConfiguration[$property] = $fieldConfiguration[$property]['EXT']['psb_foundation']['upload'] ?? [];
            $this->uploadConfiguration[$property]['allowed'] = $fieldConfiguration[$property]['allowed'] ?? null;
        }
    }

    /**
     * @param UploadedFile $uploadedFile
     *
     * @return string
     */
    private function getFileExtensionByMimeType(UploadedFile $uploadedFile): string
    {
        $mimeType = FileUtility::getMimeType($uploadedFile->getTemporaryFileName());

        return GeneralUtility::trimExplode('/', $mimeType)[1];
    }

    /**
     * @param string       $property
     * @param string       $targetFileName
     * @param UploadedFile $uploadedFile
     *
     * @return FileInterface
     */
    private function moveUploadedFileToFileStorage(
        string $property,
        string $targetFileName,
        UploadedFile $uploadedFile
    ): FileInterface {
        return $this->uploadConfiguration[$property]['storage']->addUploadedFile($uploadedFile,
            $this->uploadConfiguration[$property]['targetFolder'], $targetFileName,
            $this->uploadConfiguration[$property]['duplicationBehaviour'] ?? DuplicationBehavior::RENAME);
    }

    /**
     * @return void
     * @throws MisconfiguredTcaException
     */
    private function provideTargetFolders(): void
    {
        foreach (array_keys($this->uploadConfiguration) as $property) {
            $this->setStorageAndTargetFolder($property);
        }
    }

    /**
     * @param string $property
     *
     * @return void
     * @throws MisconfiguredTcaException
     */
    private function setStorageAndTargetFolder(string $property): void
    {
        $storage = $this->storageRepository->getDefaultStorage();

        // get upload target
        if (!empty($this->uploadConfiguration[$property]['targetFolder'])) {
            $targetFolder = $this->uploadConfiguration[$property]['targetFolder'];

            if (str_contains($targetFolder, ':')) {
                $parts = GeneralUtility::trimExplode(':', $targetFolder);

                if (2 !== count($parts)) {
                    throw new MisconfiguredTcaException(__CLASS__ . ': The configuration option "targetFolder" of the property "' . $property . '" is in an invalid format!',
                        1677590190);
                }

                $storage = $this->storageRepository->findByUid((int)$parts[0]);
                $targetFolder = $parts[1];
            }
        } else {
            $targetFolder = self::DEFAULT_UPLOAD_DIRECTORY;
        }

        if (!$storage instanceof ResourceStorageInterface) {
            throw new RuntimeException(__CLASS__ . ': Storage not found!', 1678280866);
        }

        $parentFolder = $storage->getRootLevelFolder();

        $this->uploadConfiguration[$property]['storage'] = $storage;
        $this->uploadConfiguration[$property]['targetFolder'] = $parentFolder->hasFolder($targetFolder) ? $parentFolder->getSubfolder($targetFolder) : $parentFolder->createFolder($targetFolder);
    }

    /**
     * Checks if the uploaded files match given constraints and ensures a consistent array structure for further loops.
     *
     * @param array $uploadedFilesCollection
     *
     * @return void
     */
    private function validateUploadedFiles(array &$uploadedFilesCollection): void
    {
        foreach ($uploadedFilesCollection as $property => &$uploadedFiles) {
            $uploadedFiles = ArrayUtility::guaranteeArrayType($uploadedFilesCollection[$property]);

            foreach ($uploadedFiles as $uploadedFile) {
                $this->checkForError($uploadedFile);
                $this->checkFileSize($uploadedFile, $property);
                $this->checkMimeType($uploadedFile, $property);
            }
        }
    }
}
