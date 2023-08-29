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
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use RuntimeException;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class FileReferenceService
 *
 * @package PSB\PsbFoundation\Service
 */
class FileReferenceService
{
    /**
     * @param TcaService $tcaService
     */
    public function __construct(
        protected readonly TcaService $tcaService,
    ) {
    }

    /**
     * @param AbstractEntity $domainModel
     * @param FileInterface  $file
     * @param string         $property
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function create(
        AbstractEntity $domainModel,
        FileInterface $file,
        string $property,
    ): void {
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
            $fieldName => $newId,
        ];

        // Get an instance of the DataHandler and process the data.
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start($data, []);
        $dataHandler->process_datamap();

        if (0 < count($dataHandler->errorLog)) {
            throw new RuntimeException(__CLASS__ . ': An error occured during file reference creation!', 1678275024);
        }
    }
}
