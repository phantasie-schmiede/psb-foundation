<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Service\DocComment;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019-2020 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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

use JsonException;
use PSB\PsbFoundation\Cache\CacheEntry;
use PSB\PsbFoundation\Cache\CacheEntryRepository;
use PSB\PsbFoundation\Php\ExtendedReflectionClass;
use PSB\PsbFoundation\Service\DocComment\Annotations\TCA\TcaAnnotationInterface;
use PSB\PsbFoundation\Traits\InjectionTrait;
use PSB\PsbFoundation\Utility\ObjectUtility;
use PSB\PsbFoundation\Utility\SecurityUtility;
use PSB\PsbFoundation\Utility\StringUtility;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use ReflectionClass;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException;
use function get_class;
use function in_array;
use function is_string;

/**
 * Class ParserService
 *
 * @package PSB\PsbFoundation\Service\DocCommentParserService
 */
class DocCommentParserService implements LoggerAwareInterface
{
    use InjectionTrait;
    use LoggerAwareTrait;

    public const ANNOTATION_TARGETS = [
        'CLASS'    => 'class',
        'METHOD'   => 'method',
        'PROPERTY' => 'property',
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
    private const ADD_VALUES = [
        self::ANNOTATION_TYPES['PARAM'],
        self::ANNOTATION_TYPES['THROWS'],
    ];

    /**
     * @var array
     */
    private const SINGLE_VALUES = [
        self::ANNOTATION_TYPES['PACKAGE'],
        self::ANNOTATION_TYPES['RETURN'],
        self::ANNOTATION_TYPES['VAR'],
    ];

    /**
     * @var FrontendInterface
     */
    private FrontendInterface $cache;
    /**
     * @var array
     */
    private array $namespaces;

    /**
     * @param object|string $className
     * @param string|null   $methodOrPropertyName
     *
     * @return array
     * @throws Exception
     * @throws InvalidArgumentForHashGenerationException
     * @throws JsonException
     */
    public function parsePhpDocComment($className, string $methodOrPropertyName = null): array
    {
        if (!is_string($className)) {
            $className = get_class($className);
        }

        $identifier = SecurityUtility::generateHash($className . $methodOrPropertyName);
        $cachedDocComment = $this->readFromCache($identifier);

        if (false !== $cachedDocComment) {
            return $cachedDocComment;
        }

        $parsedDocComment = [];

        $reflection = GeneralUtility::makeInstance(ExtendedReflectionClass::class, $className);

        if (null !== $methodOrPropertyName) {
            if ($reflection->hasMethod($methodOrPropertyName)) {
                $reflection = $reflection->getMethod($methodOrPropertyName);
            } elseif ($reflection->hasProperty($methodOrPropertyName)) {
                $reflection = $reflection->getProperty($methodOrPropertyName);
            }
        }

        $docComment = $reflection->getDocComment();

        if (null !== $methodOrPropertyName) {
            // If a property or method is inherited from another class, we need to get that one in order to resolve
            // imported namespaces correctly.
            $reflection = GeneralUtility::makeInstance(ExtendedReflectionClass::class,
                $reflection->getDeclaringClass()->getName());
        }

        $this->namespaces = $reflection->getImportedNamespaces();
        $this->namespaces[ObjectUtility::NAMESPACE_FALLBACK_KEY] = $reflection->getNamespaceName();

        if ($docComment) {
            $commentLines = StringUtility::explodeByLineBreaks($docComment);
            $parsedDocComment = [];
            $annotationType = self::ANNOTATION_TYPES['SUMMARY'];

            foreach ($commentLines as $commentLine) {
                $commentLine = ltrim(trim($commentLine), '/* ');

                if (StringUtility::beginsWith($commentLine, '@')) {
                    $commentLine = preg_replace('/\(/', ' (', $commentLine, 1);
                    [$annotationType, $parameters] = GeneralUtility::trimExplode(' ', ltrim($commentLine, '@'), true,
                        2);

                    if (null !== $parameters) {
                        $value = $this->processValue($annotationType, $className, $parameters);
                    } else {
                        $value = null;
                    }

                    if (is_object($value)) {
                        $annotationType = get_class(($value));
                    }

                    if (!isset($parsedDocComment[$annotationType])) {
                        $parsedDocComment[$annotationType] = [];
                    }

                    switch (true) {
                        case (in_array($annotationType, self::ADD_VALUES, true)):
                            $parsedDocComment[$annotationType][] = $value;
                            break;
                        case (in_array($annotationType, self::SINGLE_VALUES, true)):
                            if ([] !== $parsedDocComment[$annotationType]) {
                                $warning = '@' . $annotationType . ' has been overridden in ' . $className;

                                if ($methodOrPropertyName) {
                                    $warning .= ' at ' . $methodOrPropertyName;
                                }

                                $this->logger->warning($warning);
                            }

                            $parsedDocComment[$annotationType] = $value;
                            break;
                        default:
                            $parsedDocComment[$annotationType] = $value;
                    }
                } else {
                    // extract summary and description if given
                    if ('' !== $commentLine) {
                        if (isset($parsedDocComment[$annotationType])) {
                            // extend previous comment line
                            $parameters = ($parameters ?? '') . ' ' . $commentLine;

                            if (is_array($parsedDocComment[$annotationType]) && !ArrayUtility::isAssociative($parsedDocComment[$annotationType])) {
                                $indexOfLastElement = count($parsedDocComment[$annotationType]) - 1;
                                $parsedDocComment[$annotationType][$indexOfLastElement] = $this->processValue($annotationType,
                                    $className, $parameters);
                            } else {
                                $parsedDocComment[$annotationType] = $this->processValue($annotationType, $className,
                                    $parameters);
                            }
                        } else {
                            $parameters = $commentLine;
                            $parsedDocComment[$annotationType] = $parameters;
                        }
                    } elseif (self::ANNOTATION_TYPES['SUMMARY'] !== $annotationType) {
                        $annotationType = null;
                    }

                    // summary ends with a period or a blank line
                    if (self::ANNOTATION_TYPES['SUMMARY'] === $annotationType && ('.' === mb_substr($commentLine,
                                -1) || ('' === $commentLine && isset($parsedDocComment[$annotationType])))) {
                        $annotationType = self::ANNOTATION_TYPES['DESCRIPTION'];
                    }
                }
            }
        }

        $this->writeToCache($identifier, $parsedDocComment);

        return $parsedDocComment;
    }

    /**
     * @param string $value
     *
     * @return array
     */
    private function convertValueStringToPropertiesArray(string $value): array
    {
        $properties = [];
        $value = trim($value, ',()');

        // @see https://stackoverflow.com/questions/18893390/splitting-on-comma-outside-quotes
        $valueParts = preg_split('/[,;](?=(?:[^\"]*\"[^\"]*\")*[^\"]*$)/', $value);

        foreach ($valueParts as $property) {
            if (false !== mb_strpos($property, '=')) {
                [$propertyName, $propertyValue] = explode('=', $property, 2);
                $properties[trim($propertyName)] = trim($propertyValue, '"');
            }
        }

        return $properties;
    }

    /**
     * @param string|null $annotationType
     * @param string      $className
     * @param string      $value
     *
     * @return mixed
     * @throws Exception
     * @throws JsonException
     */
    private function processValue(?string $annotationType, string $className, string $value): array
    {
        $value = str_replace('self::', $className . '::', $value);

        switch ($annotationType) {
            case self::ANNOTATION_TYPES['PARAM']:
                // @TODO: take namespaces into account
                $parts = GeneralUtility::trimExplode(' ', $value, true, 3);
                [$variableType, $name, $description] = $parts;

                return [
                    'description' => $description,
                    'name'        => $name,
                    'type'        => StringUtility::convertString($variableType, true, $this->namespaces),
                ];
            case self::ANNOTATION_TYPES['RETURN']:
            case self::ANNOTATION_TYPES['THROWS']:
            case self::ANNOTATION_TYPES['VAR']:
                // @TODO: take namespaces into account
                $parts = GeneralUtility::trimExplode(' ', $value, true, 2);
                [$type, $description] = $parts;

                return [
                    'description' => $description,
                    'type'        => StringUtility::convertString($type, true, $this->namespaces),
                ];
            default:
                // check if annotation is referencing a class
                $annotationClass = ObjectUtility::getFullQualifiedClassName($annotationType, $this->namespaces);

                if (false !== $annotationClass) {
                    $reflectionClass = GeneralUtility::makeInstance(ReflectionClass::class, $annotationClass);
                    $properties = $this->convertValueStringToPropertiesArray($value);

                    if ($reflectionClass->implementsInterface(TcaAnnotationInterface::class)) {
                        /** @var TcaAnnotationInterface $annotationClass */
                        $properties = $annotationClass::propertyPreProcessor($properties);
                    }

                    $namespaces = $this->namespaces;
                    $properties = array_map(static function ($propertyValue) use ($namespaces) {
                        return StringUtility::convertString($propertyValue, true, $namespaces);
                    }, $properties);

                    return $this->get($annotationClass, $properties);
                }

                return StringUtility::convertString($value, true, $this->namespaces);
        }
    }

    /**
     * @param string $identifier
     *
     * @return bool|mixed
     * @throws Exception
     * @throws InvalidArgumentForHashGenerationException
     */
    private function readFromCache(string $identifier)
    {
        $cacheEntry = $this->get(CacheEntryRepository::class)->findByIdentifier($identifier);

        if ($cacheEntry instanceof CacheEntry) {
            return unserialize($cacheEntry->getContent(), ['allowed_classes' => true]);
        }

        return false;
    }

    /**
     * @param string $identifier
     * @param array  $parsedDocComment
     *
     * @throws Exception
     * @throws InvalidArgumentForHashGenerationException
     */
    private function writeToCache(string $identifier, array $parsedDocComment): void
    {
        $cacheEntry = $this->get(CacheEntry::class);
        $cacheEntry->setIdentifier($identifier);
        $cacheEntry->setContent(serialize($parsedDocComment));
        $this->get(CacheEntryRepository::class)->add($cacheEntry);
    }
}
