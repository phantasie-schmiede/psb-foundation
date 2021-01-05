<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Service\DocComment\Annotations;

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

use Exception;
use PSB\PsbFoundation\Service\DocComment\DocCommentParserService;
use PSB\PsbFoundation\Utility\ObjectUtility;
use PSB\PsbFoundation\Utility\ValidationUtility;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractAnnotation
 *
 * @package PSB\PsbFoundation\Service\DocComment\Annotations
 */
abstract class AbstractAnnotation
{
    /**
     * AbstractAnnotation constructor.
     *
     * Maps associative arrays to object properties. Requires the class to have appropriate setter-methods.
     *
     * @param array $data
     *
     * @throws Exception
     */
    public function __construct(array $data = [])
    {
        $debugBacktrace = debug_backtrace();
        $backtraceClasses = [];

        foreach ($debugBacktrace as $step) {
            $backtraceClasses[] = $step['class'];
        }

        if (!in_array(DocCommentParserService::class, $backtraceClasses, true)) {
            // Don't let Doctrine's AnnotationReader continue, as it might throw exceptions because it is not able to
            // resolve elements of array constants.
            return;
        }

        if (!empty($data)) {
            $reflectionClass = GeneralUtility::makeInstance(ReflectionClass::class, $this);

            foreach ($data as $propertyName => $propertyValue) {
                if (!$reflectionClass->hasProperty($propertyName)) {
                    throw new Exception(static::class . ': Class doesn\'t have a property named "' . $propertyName . '"!',
                        1583943746);
                }

                $setterMethodName = 'set' . ucfirst($propertyName);

                if ($reflectionClass->hasMethod($setterMethodName)) {
                    $reflectionMethod = GeneralUtility::makeInstance(ReflectionMethod::class, $this, $setterMethodName);
                    $reflectionMethod->invoke($this, $propertyValue);
                }
            }
        }
    }

    /**
     * @param string $targetScope
     *
     * @return array
     * @throws ReflectionException
     */
    public function toArray(string $targetScope): array
    {
        ValidationUtility::checkValueAgainstConstant(DocCommentParserService::ANNOTATION_TARGETS, $targetScope);

        return ObjectUtility::toArray($this);
    }
}
