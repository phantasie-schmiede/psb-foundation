<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\ViewHelpers\DomainModel;

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

use PSB\PsbFoundation\Service\DocComment\ValueParsers\TcaConfigParser;
use PSB\PsbFoundation\Traits\InjectionTrait;
use PSB\PsbFoundation\Utility\ExtensionInformationUtility;
use ReflectionClass;
use ReflectionException;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetTcaConfigurationViewHelper
 * @package PSB\PsbFoundation\ViewHelpers\DomainModel
 */
class GetTcaConfigurationViewHelper extends AbstractViewHelper
{
    use InjectionTrait;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('domainModel', 'string', 'full qualified class name', true, );
        $this->registerArgument('properties', 'string', 'variable name for the result', false, 'properties');
    }

    /**
     * @throws ReflectionException
     * @throws NoSuchCacheException
     */
    public function render(): void
    {
        $tableName = ExtensionInformationUtility::convertClassNameToTableName($this->arguments['domainModel']);
        $configuration = $GLOBALS['TCA'][$tableName]['columns'];
        $domainModelReflection = $this->get(ReflectionClass::class, $this->arguments['domainModel']);
        $properties = $domainModelReflection->getProperties();
        $result = [];

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $columnName = ExtensionInformationUtility::convertPropertyNameToColumnName($propertyName);

            if (true === $configuration[$columnName][TcaConfigParser::ATTRIBUTES['EDITABLE_IN_FRONTEND']]) {
                $configuration[$columnName]['config']['type'] = ucfirst($configuration[$columnName]['config']['type']);
                $result[$propertyName] = $configuration[$columnName]['config'];
            }
        }

        $this->renderingContext->getVariableProvider()->add($this->arguments['properties'], $result);
    }
}
