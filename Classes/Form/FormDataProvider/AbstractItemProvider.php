<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Form\FormDataProvider;

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

/**
 * Class AbstractItemProvider
 *
 * @package PSB\PsbFoundation\Form\FormDataProvider
 */
class AbstractItemProvider extends \TYPO3\CMS\Backend\Form\FormDataProvider\AbstractItemProvider
{
    /**
     * Validate and sanitize database row values of the select field with the given name.
     * Creates an array out of databaseRow[selectField] values.
     *
     * Used by TcaSelectItems and TcaSelectTreeItems data providers
     *
     * @param array  $result       The current result array.
     * @param string $fieldName    Name of the current select field.
     * @param array  $staticValues Array with statically defined items, item value is used as array key.
     *
     * @return array
     */
    protected function processSelectFieldValue(array $result, $fieldName, array $staticValues): array
    {
        if (!empty($fieldConfig['config']['MM']) && $result['command'] !== 'new') {
            $staticValues = [];
        }

        return parent::processSelectFieldValue($result, $fieldName, $staticValues);
    }
}
