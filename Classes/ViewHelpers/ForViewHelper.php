<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\ViewHelpers;

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

use Closure;
use PSB\PsbFoundation\Exceptions\AnnotationException;
use PSB\PsbFoundation\Service\DocComment\Annotations\TCA\Mm;
use PSB\PsbFoundation\Service\DocComment\DocCommentParserService;
use PSB\PsbFoundation\Traits\StaticInjectionTrait;
use ReflectionException;
use RuntimeException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class ForViewHelper
 *
 * This for-ViewHelper adds the feature of resolving mm-relations with the multiple attribute set to true.
 *
 * @package PSB\PsbFoundation\ViewHelpers
 */
class ForViewHelper extends \TYPO3Fluid\Fluid\ViewHelpers\ForViewHelper
{
    use StaticInjectionTrait;

    /**
     * @param array                     $arguments
     * @param Closure                   $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string
     * @throws AnnotationException
     * @throws Exception
     * @throws InvalidArgumentForHashGenerationException
     * @throws ReflectionException
     */
    public static function renderStatic(
        array $arguments,
        Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        if (isset($arguments['mmObject'])) {
            $docCommentParser = self::get(DocCommentParserService::class);
            $docComment = $docCommentParser->parsePhpDocComment($arguments['mmObject'], $arguments['mmProperty']);

            if (!isset($docComment[Mm::class])) {
                throw new RuntimeException(__CLASS__ . ': The property "' . $arguments['mmProperty'] . '" is not of TCA type mm!',
                    1584867595);
            }

            // Store each ObjectStorage element by uid.
            $eachElements = [];

            /** @var AbstractDomainObject $element */
            foreach ($arguments['each'] as $element) {
                $eachElements[$element->getUid()] = $element;
            }

            unset($arguments['each']);

            // Get all mm-relation entries.
            /** @var Mm $mm */
            $mm = $docComment[Mm::class];
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable($mm->getMm());
            $statement = $queryBuilder
                ->select('uid_foreign')
                ->from($mm->getMm())
                ->where(
                    $queryBuilder->expr()
                        ->eq('uid_local', $queryBuilder->createNamedParameter($arguments['mmObject']->getUid()))
                )
                ->orderBy('sorting')
                ->execute();

            // Rebuild the each-argument by using the ordered items of the mm-table while replacing the foreign uid with
            // the concrete object.
            while ($row = $statement->fetch()) {
                $arguments['each'][] = $eachElements[$row['uid_foreign']];
            }
        }

        return parent::renderStatic($arguments, $renderChildrenClosure, $renderingContext);
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('mmObject', 'object', 'the object that holds the local relation field');
        $this->registerArgument('mmProperty', 'string', 'the name of the mm-property to be resolved');
    }
}
