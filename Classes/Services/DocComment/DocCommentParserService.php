<?php
declare(strict_types=1);

namespace PSB\PsbFoundation\Services\DocComment;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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

use Doctrine\Common\Annotations\AnnotationReader;
use InvalidArgumentException;
use PSB\PsbFoundation\Exceptions\ImplementationException;
use PSB\PsbFoundation\Services\DocComment\ValueParsers\ValueParserInterface;
use PSB\PsbFoundation\Utilities\VariableUtility;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use ReflectionException;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use function get_class;
use function in_array;
use function is_string;

/**
 * Class ParserService
 *
 * You can register your parser for custom comments in this way (e.g. in ext_localconf.php):
 *
 * $objectManager =
 * \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
 * $docCommentParser = $objectManager->get(\PSB\PsbFoundation\Services\DocComment\DocCommentParserService::class);
 * $yourOwnValueParser = $objectManager->get(\Your\Own\ValueParser::class);
 * $docCommentParser->addValueParser($yourOwnValueParser,
 * \PSB\PsbFoundation\Services\DocComment\DocCommentParserService::VALUE_TYPES['...']);
 *
 * Keep in mind that your ValueParser has to implement
 * \PSB\PsbFoundation\Services\DocComment\ValueParsers\ValueParserInterface and the constant ANNOTATION_TYPE!
 *
 * @package PSB\PsbFoundation\Services\DocCommentParserService
 */
class DocCommentParserService implements LoggerAwareInterface, SingletonInterface
{
    use LoggerAwareTrait;

    public const VALUE_TYPES = [
        'ADD'    => 'add',
        'MERGE'  => 'merge',
        'SINGLE' => 'single',
    ];

    private const ANNOTATION_TYPES = [
        'DESCRIPTION' => 'description',
        'PACKAGE'     => 'package',
        'PARAM'       => 'param',
        'RETURN'      => 'return',
        'SUMMARY'     => 'summary',
        'THROWS'      => 'throws',
        'VAR'         => 'var',
    ];

    /**
     * @var array
     */
    private $addValues = [
        self::ANNOTATION_TYPES['PARAM'],
        self::ANNOTATION_TYPES['THROWS'],
    ];

    /**
     * @var array
     */
    private $mergeValues = [];

    /**
     * @var array
     */
    private $singleValues = [
        self::ANNOTATION_TYPES['PACKAGE'],
        self::ANNOTATION_TYPES['RETURN'],
        self::ANNOTATION_TYPES['VAR'],
    ];

    /**
     * @var array
     */
    private $valueParser = [];

    /**
     * @param ValueParserInterface $parser    Instance of your custom parser class
     * @param string               $valueType Use constant VALUE_TYPES of this class: ADD simply adds a new item to the
     *                                        result array; MERGE merges the item with the result array; SINGLE allows
     *                                        only one occurrence of this type per block
     *
     * @throws \Exception
     */
    public function addValueParser(
        ValueParserInterface $parser,
        string $valueType
    ): void {
        if (!\defined(get_class($parser).'::ANNOTATION_TYPE')) {
            throw new ImplementationException(get_class($parser).' has to define a constant named ANNOTATION_TYPE!',
                1541107562);
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $annotationType = $parser::ANNOTATION_TYPE;
        $this->valueParser[$annotationType] = $parser;

        switch ($valueType) {
            case self::VALUE_TYPES['ADD']:
                $this->addValues[] = $annotationType;
                break;
            case self::VALUE_TYPES['MERGE']:
                $this->mergeValues[] = $annotationType;
                break;
            case self::VALUE_TYPES['SINGLE']:
                $this->singleValues[] = $annotationType;
                break;
            default:
                throw new InvalidArgumentException($valueType.' is no valid value type! Use a value of this constant to provide a valid type: \PSB\PsbFoundation\Services\DocComment\DocCommentParserService::VALUE_TYPES',
                    1541348283);
        }

        AnnotationReader::addGlobalIgnoredName($annotationType);
    }

    /**
     * @param object|string $class
     * @param string|null   $methodOrPropertyName
     *
     * @return array|null
     * @throws ReflectionException
     */
    public function parsePhpDocComment($class, string $methodOrPropertyName = null): ?array
    {
        $parsedDocComment = null;

        /** @var \ReflectionClass $reflection */
        $reflection = GeneralUtility::makeInstance(\ReflectionClass::class, $class);

        if (null !== $methodOrPropertyName) {
            if ($reflection->hasMethod($methodOrPropertyName)) {
                $reflection = $reflection->getMethod($methodOrPropertyName);
            } elseif ($reflection->hasProperty($methodOrPropertyName)) {
                $reflection = $reflection->getProperty($methodOrPropertyName);
            }
        }

        $docComment = $reflection->getDocComment();

        if ($docComment) {
            $commentLines = preg_split('/(\r\n|\n|\r)/', $reflection->getDocComment());
            $parsedDocComment = [];
            $annotationType = self::ANNOTATION_TYPES['SUMMARY'];

            foreach ($commentLines as $commentLine) {
                $commentLine = ltrim(trim($commentLine), '/* ');

                if (0 === strpos($commentLine, '@')) {
                    $parts = GeneralUtility::trimExplode(' ', substr($commentLine, 1), true, 2);
                    [$annotationType, $parameters] = $parts;
                    $value = $this->processValue($annotationType, $parameters);

                    if (!isset($parsedDocComment[$annotationType])) {
                        $parsedDocComment[$annotationType] = [];
                    }

                    switch (true) {
                        case (in_array($annotationType, $this->addValues, true)):
                            $parsedDocComment[$annotationType][] = $value;
                            break;
                        case (in_array($annotationType, $this->mergeValues, true)):
                            ArrayUtility::mergeRecursiveWithOverrule($parsedDocComment[$annotationType], $value);
                            break;
                        case (in_array($annotationType, $this->singleValues, true)):
                            if ([] !== $parsedDocComment[$annotationType]) {
                                if (!is_string($class)) {
                                    $class = get_class($class);
                                }

                                $warning = '@'.$annotationType.' has been overridden in '.$class;

                                if ($methodOrPropertyName) {
                                    $warning .= ' at '.$methodOrPropertyName;
                                }

                                $this->logger->warning($warning);
                            }

                            $parsedDocComment[$annotationType] = $value;
                            break;
                        default:
                            // this case is not possible
                    }
                } else {
                    // extract summary and description if given
                    if ('' !== $commentLine) {
                        if (isset($parsedDocComment[$annotationType])) {
                            $parameters = ($parameters ?? '').' '.$commentLine;

                            if (is_array($parsedDocComment[$annotationType]) && VariableUtility::isNumericArray($parsedDocComment[$annotationType])) {
                                $indexOfLastElement = count($parsedDocComment[$annotationType]) - 1;
                                $parsedDocComment[$annotationType][$indexOfLastElement] = $this->processValue($parameters,
                                    $annotationType);
                            } else {
                                $parsedDocComment[$annotationType] = $this->processValue($annotationType, $parameters);
                            }
                        } else {
                            $parameters = $commentLine;
                            $parsedDocComment[$annotationType] = $parameters;
                        }
                    } elseif (self::ANNOTATION_TYPES['SUMMARY '] !== $annotationType) {
                        $annotationType = null;
                    }

                    // summary ends with a period or a blank line
                    if (self::ANNOTATION_TYPES['SUMMARY '] === $annotationType && ('.' === substr($commentLine,
                                -1) || ('' === $commentLine && isset($parsedDocComment[$annotationType])))) {
                        $annotationType = self::ANNOTATION_TYPES['DESCRIPTION'];
                    }
                }
            }
        }

        return $parsedDocComment;
    }

    /**
     * @param string|null $annotationType
     * @param mixed       $value
     *
     * @return mixed
     */
    private function processValue(?string $annotationType, $value)
    {
        if (isset($this->valueParser[$annotationType])) {
            return $this->valueParser[$annotationType]->processValue($value);
        }

        if (null !== $value) {
            switch ($annotationType) {
                case self::ANNOTATION_TYPES['PARAM']:
                    $parts = GeneralUtility::trimExplode(' ', $value, true, 3);
                    [$variableType, $name, $description] = $parts;

                    return [
                        'description' => $description,
                        'name'        => $name,
                        'type'        => $variableType,
                    ];
                case self::ANNOTATION_TYPES['RETURN']:
                case self::ANNOTATION_TYPES['THROWS']:
                case self::ANNOTATION_TYPES['VAR']:
                    $parts = GeneralUtility::trimExplode(' ', $value, true, 2);
                    [$type, $description] = $parts;

                    return [
                        'description' => $description,
                        'type'        => $type,
                    ];
                default:
                    return $value;
            }
        } else {
            return [];
        }
    }
}
