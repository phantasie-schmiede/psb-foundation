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
use RuntimeException;
use Symfony\Component\HttpFoundation\File\Exception\IniSizeFileException;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Mvc\Request;

/**
 * Class FileService
 *
 * @package PSB\PsbFoundation\Service
 */
class UploadService
{
    public const DEFAULT_UPLOAD_DIRECTORY = 'user_upload';

    private array $fieldConfiguration = [];

    /**
     * @param StorageRepository $storageRepository
     * @param TcaService        $tcaService
     */
    public function __construct(
        protected readonly StorageRepository $storageRepository,
        protected readonly TcaService $tcaService,
    ) {
    }

    /**
     * The name of the upload form fields have to be identical with the properties' names!
     *
     * @param AbstractEntity $domainModel
     * @param Request        $request
     *
     * @return void
     * @throws InsufficientFolderAccessPermissionsException
     * @throws MisconfiguredTcaException
     */
    public function fromRequest(AbstractEntity $domainModel, Request $request): void
    {
        $uploadedFilesCollection = $request->getUploadedFiles()[ContextUtility::getPluginSignatureFromRequest($request)];

        if (empty($uploadedFilesCollection)) {
            return;
        }

        // Preparation
        $properties = array_keys($uploadedFilesCollection);
        $this->collectFieldConfigurations($domainModel, $properties);
        $this->validateUploadedFiles($uploadedFilesCollection);
        $this->provideTargetFolders($properties);

        // Execution
        foreach ($uploadedFilesCollection as $property => $uploadedFiles) {
            foreach ($uploadedFiles as $uploadedFile) {
                $file = $this->moveUploadedFileToFileStorage($property, $uploadedFile);
                $this->createFileReference($domainModel, $file, $property);
            }
        }
    }

    /**
     * @param UploadedFile $file
     * @param string       $property
     *
     * @return void
     */
    private function checkFileSize(UploadedFile $file, string $property): void
    {
        if (isset($this->fieldConfiguration[$property]['upload']['maxSize']) && (int)$this->fieldConfiguration[$property]['upload']['maxSize'] < $file->getSize()) {
            throw new RuntimeException('Too large!');
        }
    }

    /**
     * @param UploadedFile $file
     *
     * @return void
     */
    private function checkForError(UploadedFile $file): void
    {
        switch ($file->getError()) {
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
     * @param UploadedFile $file
     * @param string       $property
     *
     * @return void
     */
    private function checkMimeType(UploadedFile $file, string $property): void
    {
        if (isset($this->fieldConfiguration[$property]['allowed']) && !in_array(FileUtility::getMimeType($file->getTemporaryFileName()),
                $this->fieldConfiguration[$property]['allowed'])) {
            throw new RuntimeException('File type not allowed!');
        }
    }

    /**
     * @param AbstractEntity $domainModel
     * @param array          $properties
     *
     * @return void
     */
    private function collectFieldConfigurations(AbstractEntity $domainModel, array $properties): void
    {
        foreach ($properties as $property) {
            // get processing information from TCA
            $this->fieldConfiguration[$property] = $this->tcaService->getConfigurationForPropertyOfDomainModel($domainModel,
                $property);
        }
    }

    /**
     * @param AbstractEntity $domainModel
     * @param FileInterface  $file
     * @param string         $property
     *
     * @return void
     */
    private function createFileReference(AbstractEntity $domainModel, FileInterface $file, string $property): void
    {
        $fieldName = $this->tcaService->convertPropertyNameToColumnName($property);
        $tableName = $this->tcaService->convertClassNameToTableName($domainModel::class);

        // Assemble DataHandler data.
        $newId = 'NEW1234'; // This will be replaced during DataHandler processing.
        $data = [];
        $data['sys_file_reference'][$newId] = [
            'fieldname'   => $fieldName,
            'pid'         => $domainModel->getPid(),
            'table_local' => 'sys_file',
            'tablenames'  => $tableName,
            'uid_foreign' => $domainModel->getUid(),
            'uid_local'   => $file->getUid(),
        ];
        $data[$tableName][$domainModel->getUid()] = [
            $fieldName => $newId
        ];

        // Get an instance of the DataHandler and process the data.
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start($data, []);
        $dataHandler->process_datamap();

        if (0 < count($dataHandler->errorLog)) {
            // Handle errors
        }
    }

    /**
     * @param string       $property
     * @param UploadedFile $uploadedFile
     *
     * @return FileInterface
     */
    private function moveUploadedFileToFileStorage(string $property, UploadedFile $uploadedFile): FileInterface
    {
        return $this->fieldConfiguration[$property]['storage']->addUploadedFile($uploadedFile, $this->fieldConfiguration[$property]['targetFolder'], $targetFileName ?? null,
            DuplicationBehavior::RENAME);
    }

    /**
     * @param array $uploadedFilesCollection
     *
     * @return void
     * @throws MisconfiguredTcaException
     */
    private function provideTargetFolders(array $uploadedFilesCollection): void
    {
        foreach ($uploadedFilesCollection as $property => $uploadedFiles) {
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
        if (isset($this->fieldConfiguration[$property]['upload']['targetDirectory']) && !empty($this->fieldConfiguration[$property]['upload']['targetDirectory'])) {
            $targetFolder = $this->fieldConfiguration[$property]['upload']['targetDirectory'];

            if (str_contains($targetFolder, ':')) {
                $parts = GeneralUtility::trimExplode(':', $targetFolder);

                if (2 !== count($parts)) {
                    throw new MisconfiguredTcaException(__CLASS__ . ': The configuration option "targetDirectory" of the property "' . $property . '" is in an invalid format!',
                        1677590190);
                }

                $storage = $this->storageRepository->findByUid((int)$parts[0]);
                $targetFolder = $parts[1];
            }
        } else {
            $targetFolder = self::DEFAULT_UPLOAD_DIRECTORY;
        }

        $parentFolder = $storage->getRootLevelFolder();

        $this->fieldConfiguration[$property]['storage'] = $storage;
        $this->fieldConfiguration[$property]['targetFolder'] = $parentFolder->hasFolder($targetFolder) ? $parentFolder->getSubfolder($targetFolder) : $parentFolder->createFolder($targetFolder);
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
            $uploadedFiles = ArrayUtility::guaranteeArrayType($uploadedFiles);

            foreach ($uploadedFiles as $file) {
                $this->checkForError($file);
                $this->checkFileSize($file, $property);
                $this->checkMimeType($file, $property);
            }
        }
    }
}
