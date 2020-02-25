<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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

use PSB\PsbFoundation\Domain\Repository\AbstractRepository;
use PSB\PsbFoundation\Traits\InjectionTrait;
use PSB\PsbFoundation\Utility\ExtensionInformationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentNameException;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Property\Exception;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationBuilder;
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
    use InjectionTrait;

    /**
     * @var string
     */
    protected $domainModel;

    /**
     * @var AbstractRepository
     */
    protected $repository;

    /**
     * The constructor determines the related model and repository classes of the instantiated controller following
     * Extbase conventions.
     */
    public function __construct()
    {
        [$vendorName, $extensionName] = GeneralUtility::trimExplode('\\', get_class($this));
        $domainModelName = ExtensionInformationUtility::convertControllerClassToBaseName(get_class($this));
        $this->setDomainModel(implode('\\', [$vendorName, $extensionName, 'Domain\Model', $domainModelName]));
        $this->repository = $this->get(implode('\\',
            [$vendorName, $extensionName, 'Domain\Repository', $domainModelName . 'Repository']));
    }

    /**
     * @param AbstractEntity $record
     * @PSB\PsbFoundation\Plugin\Action \PSB\PsbFoundation\Service\DocComment\ValueParsers\PluginActionParser::FLAGS[UNCACHED]
     *
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     * @throws IllegalObjectTypeException
     */
    public function createAction(AbstractEntity $record): void
    {
        $this->repository->add($record);
        $this->redirect('list');
    }

    /**
     * @param AbstractEntity $record
     * @PSB\PsbFoundation\Plugin\Action \PSB\PsbFoundation\Service\DocComment\ValueParsers\PluginActionParser::FLAGS[UNCACHED]
     *
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     * @throws IllegalObjectTypeException
     */
    public function deleteAction(AbstractEntity $record): void
    {
        $this->repository->remove($record);
        $this->redirect('list');
    }

    /**
     * @param AbstractEntity $record
     * @PSB\PsbFoundation\Plugin\Action \PSB\PsbFoundation\Service\DocComment\ValueParsers\PluginActionParser::FLAGS[UNCACHED]
     */
    public function editAction(AbstractEntity $record): void
    {
        $this->view->assign('record', $record);
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

    /**
     * Incoming form data is mapped to the correct domain model.
     *
     * @throws Exception
     * @throws InvalidArgumentNameException
     * @throws NoSuchArgumentException
     */
    public function initializeAction(): void
    {
        if ($this->request->hasArgument('record')
            && is_array($this->request->getArgument('record'))
        ) {
            $mappingConfiguration = $this->get(PropertyMappingConfigurationBuilder::class)->build();
            $mappingConfiguration->allowAllProperties();
            $record = $this->get(PropertyMapper::class)->convert(
                $this->request->getArgument('record'),
                $this->getDomainModel(true),
                $mappingConfiguration
            );
            $this->request->setArgument('record', $record);
        }
    }

    /**
     * @param ViewInterface $view
     */
    public function initializeView(ViewInterface $view): void
    {
        $this->view->assignMultiple([
            'domainModel'  => $this->getDomainModel(),
            'extensionKey' => $this->request->getControllerExtensionKey(),
        ]);
    }

    /**
     * @PSB\PsbFoundation\Plugin\Action \PSB\PsbFoundation\Service\DocComment\ValueParsers\PluginActionParser::FLAGS[DEFAULT]
     */
    public function listAction(): void
    {
        $this->view->assign('records', $this->repository->findAll());
    }

    /**
     * @param AbstractEntity $record
     */
    public function newAction(AbstractEntity $record = null): void
    {
        $this->view->assign('record', $record);
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
     * @PSB\PsbFoundation\Plugin\Action \PSB\PsbFoundation\Service\DocComment\ValueParsers\PluginActionParser::FLAGS[UNCACHED]
     *
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function updateAction(AbstractEntity $record): void
    {
        $this->repository->update($record);
        $this->redirect('list');
    }
}
