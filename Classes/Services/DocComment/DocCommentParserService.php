<?php
declare(strict_types=1);

namespace PS\PsFoundation\Services\DocComment;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018 Daniel Ablass <dn@phantasie-schmiede.de>, Phantasie-Schmiede
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
use PS\PsFoundation\Services\DocComment\ValueParsers\ValueParserInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ParserService
 *
 * You can register your parser for custom comments in this way (e.g. in ext_localconf.php):
 *
 * $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
 * $docCommentParser = $objectManager->get(\PS\PsFoundation\Services\DocComment\DocCommentParserService::class);
 * $yourOwnValueParser = $objectManager->get(\Your\Own\ValueParser::class);
 * $docCommentParser->addValueParser($yourOwnValueParser, \PS\PsFoundation\Services\DocComment\DocCommentParserService::VALUE_TYPES['...']);
 *
 * Keep in mind that your ValueParser has to implement \PS\PsFoundation\Services\DocComment\ValueParsers\ValueParserInterface
 * and the constant ANNOTATION_TYPE!
 *
 * @package PS\PsFoundation\Services\DocCommentParserService
 */
class DocCommentParserService implements LoggerAwareInterface, SingletonInterface
{
    use LoggerAwareTrait;

    public const VALUE_TYPES = [
        'ADD'    => 'add',
        'MERGE'  => 'merge',
        'SINGLE' => 'single',
    ];

    private const SECTION_SUMMARY     = 'summary';
    private const SECTION_DESCRIPTION = 'description';
    private const SECTION_PARAM       = 'param';
    private const SECTION_RETURN      = 'return';
    private const SECTION_THROWS      = 'throws';
    private const SECTION_VAR         = 'var';

    /**
     * @var array
     */
    private $addValues = [
        self::SECTION_PARAM,
        self::SECTION_THROWS,
    ];

    /**
     * @var array
     */
    protected $mergeValues = [];

    /**
     * @var array
     */
    private $singleValues = [
        'package',
        self::SECTION_RETURN,
        self::SECTION_VAR,
    ];

    /**
     * @var array
     */
    private $valueParser = [];

    /**
     * @param ValueParserInterface $parser Instance of your custom parser class
     * @param string $valueType Use constant VALUE_TYPES of this class: ADD simply adds a new item to the result array; MERGE merges the item with the result array; SINGLE allows only one occurrence of this type per block
     *
     * @throws \Exception
     */
    public function addValueParser(
        ValueParserInterface $parser,
        string $valueType
    ): void {
        if (!\defined(\get_class($parser).'::ANNOTATION_TYPE')) {
            throw new \Exception(\get_class($parser).' has to define a constant named ANNOTATION_TYPE!', 1541107562);
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
                throw new \Exception($valueType.' is no valid value type! Use this constant to provide a valid type: \PS\PsFoundation\Services\DocComment\DocCommentParserService::VALUE_TYPES', 1541348283);
        }

        AnnotationReader::addGlobalIgnoredName($annotationType);
    }

    /**
     * @param object|string $class
     * @param string|null $methodOrPropertyName
     *
     * @return array|null
     */
    public function parsePhpDocComment($class, string $methodOrPropertyName = null): ?array
    {
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
            $section = self::SECTION_SUMMARY;

            foreach ($commentLines as $commentLine) {
                $commentLine = ltrim(trim($commentLine), '/* ');
                if (0 === strpos($commentLine, '@')) {
                    $parts = GeneralUtility::trimExplode(' ', substr($commentLine, 1), true, 2);
                    $annotationType = array_shift($parts);

                    if (isset($this->valueParser[$annotationType])) {
                        $value = $this->valueParser[$annotationType]->processValue(implode($parts));
                    } elseif (!empty($parts)) {
                        switch ($annotationType) {
                            case self::SECTION_PARAM:
                                $parts = GeneralUtility::trimExplode(' ', $parts[0], true, 3);
                                [$variableType, $name, $description] = $parts;
                                $value = [
                                    'description' => $description,
                                    'name'        => $name,
                                    'type'        => $variableType,
                                ];
                                break;
                            case self::SECTION_RETURN:
                            case self::SECTION_THROWS:
                            case self::SECTION_VAR:
                                $parts = GeneralUtility::trimExplode(' ', $parts[0], true, 2);
                                [$type, $description] = $parts;
                                $value = [
                                    'description' => $description,
                                    'type'        => $type,
                                ];
                                break;
                            default:
                                $value = $parts[0];
                        }
                    } else {
                        $value = [];
                    }

                    $parsedDocComment[$annotationType] = [];

                    switch (true) {
                        case (\in_array($annotationType, $this->addValues, true)):
                            $parsedDocComment[$annotationType][] = $value;
                            break;
                        case (\in_array($annotationType, $this->mergeValues, true)):
                            ArrayUtility::mergeRecursiveWithOverrule($parsedDocComment[$annotationType], $value);
                            break;
                        case (\in_array($annotationType, $this->singleValues, true)):
                            if (isset($parsedDocComment[$annotationType])) {
                                if (!\is_string($class)) {
                                    $class = \get_class($class);
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
                        if (isset($parsedDocComment[$section])) {
                            $parsedDocComment[$section] .= ' '.$commentLine;
                        } else {
                            $parsedDocComment[$section] = $commentLine;
                        }
                    }

                    // summary ends with a period or a blank line
                    if (self::SECTION_SUMMARY === $section && ('.' === substr($commentLine,
                                -1) || ('' === $commentLine && isset($parsedDocComment[$section])))) {
                        $section = self::SECTION_DESCRIPTION;
                    }
                }
            }

            return $parsedDocComment;
        }

        return null;
    }
}
