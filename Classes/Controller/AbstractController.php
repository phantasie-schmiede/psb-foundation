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

namespace PSB\PsbFoundation\Controller;

use PSB\PsbFoundation\Annotation\PluginAction;
use PSB\PsbFoundation\Service\ExtensionInformationService;
use PSB\PsbFoundation\Traits\PropertyInjection\PropertyMapperTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentNameException;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Property\Exception;
use function get_class;

/**
 * Class AbstractController
 *
 * This abstract controller provides all basic methods for FE-editable domain models: list, show, new, create, edit,
 * update and delete. If you follow the naming conventions of Extbase, you only need to extend this class.
 *
 * @package PSB\PsbFoundation\Controller
 */
abstract class AbstractController extends ActionController
{
    use PropertyMapperTrait;

    /**
     * @var string
     */
    protected string $domainModel;

    /**
     * @var ExtensionInformationService
     */
    protected ExtensionInformationService $extensionInformationService;

    /**
     * @var Repository
     */
    protected Repository $repository;

    /**
     * The constructor determines the related model and repository classes of the instantiated controller following
     * Extbase conventions.
     *
     * @param ExtensionInformationService $extensionInformationService
     */
    public function __construct(ExtensionInformationService $extensionInformationService)
    {
        [$vendorName, $extensionName] = GeneralUtility::trimExplode('\\', get_class($this));
        $this->extensionInformationService = $extensionInformationService;
        $domainModelName = $this->extensionInformationService->convertControllerClassToBaseName(get_class($this));
        $this->setDomainModel(implode('\\', [
            $vendorName,
            $extensionName,
            'Domain\Model',
            $domainModelName,
        ]));

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->repository = GeneralUtility::makeInstance(implode('\\', [
            $vendorName,
            $extensionName,
            'Domain\Repository',
            $domainModelName . 'Repository',
        ]));
    }

    /**
     * @param AbstractEntity $record
     * @param string|null    $returnUrl
     *
     * @PluginAction(uncached=true)
     * @throws IllegalObjectTypeException
     * @throws StopActionException
     */
    public function createAction(AbstractEntity $record, string $returnUrl = null): void
    {
        $this->repository->add($record);

        if (null !== $returnUrl) {
            $this->redirectToUri($returnUrl);
        } else {
            $this->redirect('list');
        }
    }

    /**
     * @param AbstractEntity $record
     * @param string|null    $returnUrl
     *
     * @PluginAction(uncached=true)
     * @throws IllegalObjectTypeException
     * @throws StopActionException
     */
    public function deleteAction(AbstractEntity $record, string $returnUrl = null): void
    {
        $this->repository->remove($record);

        if (null !== $returnUrl) {
            $this->redirectToUri($returnUrl);
        } else {
            $this->redirect('list');
        }
    }

    /**
     * @param AbstractEntity $record
     * @param string|null    $returnUrl
     *
     * @PluginAction(uncached=true)
     */
    public function editAction(AbstractEntity $record, string $returnUrl = null): void
    {
        $this->view->assignMultiple([
            'record'    => $record,
            'returnUrl' => $returnUrl,
        ]);
    }

    /**
     * Incoming form data is mapped to the correct domain model.
     *
     * @throws Exception
     * @throws InvalidArgumentNameException
     * @throws NoSuchArgumentException
     */
    public function initializeAction(): void
    {
        if ($this->request->hasArgument('record')) {
            $record = $this->request->getArgument('record');

            if (is_array($record)) {
                $mappingConfiguration = $this->arguments->getArgument('record')->getPropertyMappingConfiguration();
                $mappingConfiguration->allowAllProperties();
                $record = $this->propertyMapper->convert(
                    $this->request->getArgument('record'),
                    $this->getDomainModel(true),
                    $mappingConfiguration
                );
            } else {
                $record = $this->repository->findByUid((int)$record);
            }

            $this->request->setArgument('record', $record);
        }
    }

    /**
     * @param ViewInterface $view
     */
    public function initializeView(ViewInterface $view): void
    {
        $this->view->assignMultiple([
            'domainModel'      => $this->getDomainModel(),
            'domainModelClass' => $this->getDomainModel(true),
            'extensionKey'     => $this->request->getControllerExtensionKey(),
        ]);
    }

    /**
     * @PluginAction(default=true)
     */
    public function listAction(): void
    {
        $this->view->assign('records', $this->repository->findAll());
    }

    /**
     * @param AbstractEntity|null $record
     * @param string|null         $returnUrl
     *
     * @PluginAction(uncached=true)
     */
    public function newAction(AbstractEntity $record = null, string $returnUrl = null): void
    {
        $this->view->assignMultiple([
            'record'    => $record,
            'returnUrl' => $returnUrl,
        ]);
    }

    /**
     * @param AbstractEntity $record
     *
     * @PluginAction()
     */
    public function showAction(AbstractEntity $record): void
    {
        $this->view->assign('record', $record);
    }

    /**
     * @param AbstractEntity $record
     * @param string|null    $returnUrl
     *
     * @PluginAction(uncached=true)
     * @throws IllegalObjectTypeException
     * @throws StopActionException
     * @throws UnknownObjectException
     */
    public function updateAction(AbstractEntity $record, string $returnUrl = null): void
    {
        $this->repository->update($record);

        if (null !== $returnUrl) {
            $this->redirectToUri($returnUrl);
        } else {
            $this->redirect('list');
        }
    }

    /**
     * @param bool $fullQualifiedClassName If set to true, function returns the full qualified class name
     *
     * @return string
     */
    public function getDomainModel(bool $fullQualifiedClassName = false): string
    {
        if (false === $fullQualifiedClassName) {
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
}
