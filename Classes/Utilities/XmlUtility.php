<?php
declare(strict_types=1);

namespace PS\PsFoundation\Utilities;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 PSG Web Team <webdev@plan.de>, PSG Plan Service Gesellschaft mbH
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

/**
 * Class XmlUtility
 * @package PS\PsFoundation\Utilities
 */
class XmlUtility
{
    public const ATTRIBUTES_KEY = '_attributes';

    /**
     * @param array $array
     *
     * @return string
     */
    public static function convertArrayToXml(array $array): string
    {
        $xml = '';

        foreach ($array as $key => $value) {
            if (is_string($key)) {
                $xml .= '<'.$key;

                if (is_array($value) && isset($value[self::ATTRIBUTES_KEY]) && is_array($value[self::ATTRIBUTES_KEY])) {
                    foreach ($value[self::ATTRIBUTES_KEY] as $attributeName => $attributeValue) {
                        $xml .= ' '.$attributeName.'="'.$attributeValue.'"';
                    }

                    unset($value[self::ATTRIBUTES_KEY]);
                }

                $xml .= '>';
            }

            if (is_array($value)) {
                $xml .= self::convertArrayToXml($value);
            } else {
                $xml .= $value;
            }

            if (is_string($key)) {
                $xml .= '</'.$key.'>';
            }
        }

        return $xml;
    }
}
