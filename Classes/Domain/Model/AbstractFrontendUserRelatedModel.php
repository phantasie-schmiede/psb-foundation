<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Domain\Model;

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

use PSB\PsbFoundation\Service\DocComment\Annotations\TCA\Select;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;

/**
 * Class AbstractFrontendUserRelatedModel
 *
 * By extending this class you can connect your model with a frontend user.
 *
 * @package PSB\PsbFoundation\Domain\Model
 */
abstract class AbstractFrontendUserRelatedModel extends AbstractModelWithDataManipulationProtection
{
    /**
     * @var FrontendUser|null
     * @Select(editableInFrontend=false, linkedModel="FrontendUser")
     */
    protected ?FrontendUser $frontendUser = null;

    /**
     * @return FrontendUser|null
     */
    public function getFrontendUser(): ?FrontendUser
    {
        return $this->frontendUser;
    }

    /**
     * @param FrontendUser $frontendUser
     */
    public function setFrontendUser(FrontendUser $frontendUser): void
    {
        $this->frontendUser = $frontendUser;
    }
}
