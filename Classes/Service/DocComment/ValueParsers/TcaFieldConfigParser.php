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

use PSB\PsbFoundation\Exceptions\AnnotationException;
use PSB\PsbFoundation\Utility\ExtensionInformationUtility;
use ReflectionException;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class TcaFieldConfigParser
 *
 * Use this in the annotations of your domain model properties. Possible attributes are all those listed in the
 * official TCA documentation (depends on the type you defined with the TcaConfigParser annotation):
 * https://docs.typo3.org/m/typo3/reference-tca/master/en-us/ColumnsConfig/Index.html#columns-types
 *
 * @package PSB\PsbFoundation\Service\DocComment\ValueParsers
 */
class TcaFieldConfigParser extends AbstractValuePairsParser
{
    public const ANNOTATION_TYPE = 'PSB\PsbFoundation\Tca\FieldConfig';

    /**
     * @param string      $className
     * @param string|null $valuePairs
     *
     * @return mixed
     * @throws AnnotationException
     * @throws NoSuchCacheException
     * @throws ReflectionException
     */
    public function processValue(string $className, ?string $valuePairs)
    {
        $result = parent::processValue($className, $valuePairs);

        if ('select' === $result['type']) {
            // instead of directly specifying a foreign table, it is possible to specify a domain model class instead
            if (isset ($result['linked_model'])) {
                // allow shorthand syntax for simple relations between models in the same domain
                if (false === mb_strpos($result['linked_model'], 'Domain\Model')) {
                    [$vendorName, $extensionName] = GeneralUtility::trimExplode('\\', $className);
                    $result['linked_model'] = implode('\\',
                        [$vendorName, $extensionName, 'Domain\Model', $result['linked_model']]);
                }

                $domainModelTable = ExtensionInformationUtility::convertClassNameToTableName($result['linked_model']);
                $result['foreign_table'] = $domainModelTable;
                unset($result['linked_model']);
            }

            if (isset ($result['items']) && is_array($result['items'])) {
                // transform associative array to simple array for TCA
                $result['items'] = array_map(static function ($key, $value) {
                    return [ucwords(str_replace('_', ' ', mb_strtolower($key))), $value];
                }, array_keys($result['items']), array_values($result['items']));
            }
        }

        return $result;
    }
}
