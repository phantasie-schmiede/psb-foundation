<?php
declare(strict_types=1);

namespace PS\PsFoundation\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 Daniel Ablass <dn@phantasie-schmiede.de>, Phantasie-Schmiede
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

use PS\PsFoundation\Domain\Repository\AbstractRepository;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class AbstractController
 * @package PS\PsFoundation\Controller
 */
class AbstractController extends ActionController
{
    /**
     * @var string
     */
    protected $domainModel;

    /**
     * @var AbstractRepository
     */
    protected $repository;

    /**
     * AbstractController constructor.
     * Determines related model and repository classes from controller
     */
    public function __construct()
    {
        parent::__construct();

        [$void, $vendorName, $extensionName, $path, $className] = explode('\\', \get_class($this));
        $path = 'Domain\Model';
        $className = substr($className, 0, -10); // remove 'Controller'
        $this->setDomainModel(implode('\\', [$void, $vendorName, $extensionName, $path, $className]));

        $path = 'Domain\Repository';
        $className .= 'Repository';
        $this->repository = $this->objectManager->get(implode('\\',
            [$void, $vendorName, $extensionName, $path, $className]));
    }

    /**
     * @param bool $fqcn If set to true, function returns the full qualified class name
     *
     * @return string
     */
    public function getDomainModel(bool $fqcn = false): string
    {
        if (false === $fqcn) {
            $classNameParts = explode('\\', $this->domainModel);

            return array_pop($classNameParts);
        }

        return $this->domainModel;
    }

    /**
     * @param string $domainModel
     */
    public function setDomainModel(string $domainModel): void
    {
        $this->domainModel = $domainModel;
    }

    /**
     * @param AbstractEntity $record
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function createAction(AbstractEntity $record): void
    {
        $this->repository->add($record);
        $this->redirect('list');
    }

    /**
     * @param AbstractEntity $record
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function deleteAction(AbstractEntity $record): void
    {
        $this->repository->remove($record);
        $this->redirect('list');
    }

    /**
     * @param AbstractEntity $record
     */
    public function editAction(AbstractEntity $record): void
    {
        $this->view->assignMultiple([
            'domainModel' => $this->getDomainModel(),
            'record'      => $record,
        ]);
    }

    /**
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    public function initializeAction(): void
    {
        if ($this->request->hasArgument('record') && get_class($this->request->getArgument('record')) !== $this->getDomainModel(true)) {
            throw new \InvalidArgumentException(__CLASS__.': Argument "record" has to be an instance of '.$this->getDomainModel(true),
                1551301206);
        }
    }

    public function listAction(): void
    {
        $this->view->assignMultiple([
            'domainModel' => $this->getDomainModel(),
            'records'     => $this->repository->findAll(),
        ]);
    }

    /**
     * @param AbstractEntity $record
     */
    public function newAction(AbstractEntity $record = null): void
    {
        $this->view->assignMultiple([
            'domainModel' => $this->getDomainModel(),
            'record'      => $record,
        ]);
    }

    /**
     * @param AbstractEntity $record
     */
    public function showAction(AbstractEntity $record): void
    {
        $this->view->assign('record', $record);
    }

    /**
     * @param AbstractEntity $record
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function updateAction(AbstractEntity $record): void
    {
        $this->repository->update($record);
        $this->redirect('list');
    }
}
