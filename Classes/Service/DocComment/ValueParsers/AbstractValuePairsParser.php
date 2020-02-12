<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Service\DocComment\ValueParsers;

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

use Exception;
use PSB\PsbFoundation\Exceptions\AnnotationException;
use PSB\PsbFoundation\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractValuePairsParser
 * @package PSB\PsbFoundation\Service\DocComment\ValueParsers
 */
abstract class AbstractValuePairsParser implements ValueParserInterface
{
    /**
     * @param string      $className
     * @param string|null $valuePairs
     *
     * @return mixed
     * @throws AnnotationException
     */
    public function processValue(string $className, ?string $valuePairs)
    {
        if (null === $valuePairs) {
            /** @noinspection PhpUndefinedClassConstantInspection */
            throw new AnnotationException(static::ANNOTATION_TYPE . ' must be followed by value pairs like "key=value"!',
                1541619320);
        }

        $result = [];
        $valueParts = GeneralUtility::trimExplode(';', $valuePairs, true);

        foreach ($valueParts as $part) {
            [$key, $value] = GeneralUtility::trimExplode('=', $part, false, 2);
            $value = str_replace('self::', $className . '::', $value);
            $result[$key] = StringUtility::convertString($value);
        }

        return $result;
    }
}
