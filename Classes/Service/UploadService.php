<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Service;

use PSB\PsbFoundation\Service\Configuration\TcaService;
use PSB\PsbFoundation\Utility\ArrayUtility;
use PSB\PsbFoundation\Utility\ContextUtility;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Mvc\Request;
use function get_class;

/**
 * Class FileService
 *
 * @package PSB\PsbFoundation\Service
 */
class UploadService
{
    /**
     * @param StorageRepository $storageRepository
     * @param TcaService        $tcaService
     */
    public function __construct(
        // use ResourceFactory;
        protected readonly StorageRepository $storageRepository,
        protected readonly TcaService $tcaService,
    ) {
    }

    /**
     * @param Request             $request
     * @param AbstractEntity|null $domainModel
     *
     * @return void
     * @throws InsufficientFolderAccessPermissionsException
     */
    public function fromRequest(Request $request, AbstractEntity $domainModel = null): void
    {
        $uploadedFilesCollection = $request->getUploadedFiles()[ContextUtility::getPluginSignatureFromRequest($request)];

        if (empty($uploadedFilesCollection)) {
            return;
        }

        if (null === $domainModel) {
            // get domain model object from request
        }

        $properties = [];

        // The name of the upload form field has to be identical with the property name!
        foreach ($uploadedFilesCollection as $property => $uploadedFiles) {
            $properties[] = $property;
        }

        // get processing information from TCA
        $tableName = $this->tcaService->convertClassNameToTableName(get_class($domainModel));
        $configuration = $this->tcaService->getConfigurationForPropertyOfDomainModel($domainModel, $property);

        // get upload target
        if (isset($configuration['uploadDirectory']) && !empty($configuration['uploadDirectory'])) {
            $uploadDirectory = $configuration['uploadDirectory'];

            if (str_contains($uploadDirectory, ':')) {
                // $this->storageRepository->findByCombinedIdentifier();
            } else {
            }
        } else {
            $defaultStorage = $this->storageRepository->getDefaultStorage();
            $uploadDirectory = $defaultStorage->getFolder('/user_upload');
        }

        $uploadedFiles = ArrayUtility::guaranteeArrayType($uploadedFiles);

        foreach ($uploadedFiles as $uploadedFile) {
            // create sys_file
            // create sys_file_reference
        }
    }
}
