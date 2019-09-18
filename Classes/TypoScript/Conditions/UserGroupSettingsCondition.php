<?php

namespace PSB\PsbFoundation\TypoScript\Conditions;

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

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Configuration\TypoScript\ConditionMatching\AbstractCondition;

/**
 * Class UserGroupSettingsCondition
 * @package PSB\PsbFoundation\TypoScript\Conditions
 */
class UserGroupSettingsCondition extends AbstractCondition
{
    /**
     * Evaluate condition
     *
     * @param array $parameters
     *
     * @return bool
     */
    public function matchCondition(array $parameters): bool
    {
        if (!empty($parameters && isset($parameters[0]))) {
            $property = $parameters[0];

            /** @var BackendUserAuthentication $beUser */
            $beUser = $GLOBALS['BE_USER'];

            if (is_iterable($beUser->userGroups)) {
                foreach ($beUser->userGroups as $userGroup) {
                    if (true === (bool)$userGroup[$property]) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
