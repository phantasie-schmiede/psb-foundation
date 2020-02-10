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
     * @param string|null $valuePairs
     *
     * @return mixed
     * @throws Exception
     */
    public function processValue(?string $valuePairs)
    {
        $result = parent::processValue($valuePairs);

        // transform associative array to simple array for TCA
        if ('select' === $result['type'] && isset ($result['items']) && is_array($result['items'])) {
            $result['items'] = array_map(static function ($key, $value) {
                if (0 === mb_strpos($key, ' ')) {
                    // prettify constant names
                    return [ucwords(str_replace('_', ' ', mb_strtolower($key))), $value];
                }

                return $key;
            }, array_keys($result['items']), array_values($result['items']));
        }

        return $result;
    }
}
