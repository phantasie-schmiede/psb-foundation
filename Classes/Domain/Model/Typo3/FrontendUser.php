<?php
declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace PSB\PsbFoundation\Domain\Model\Typo3;

/**
 * Class FrontendUser
 *
 * @package PSB\PsbFoundation\Domain\Model\Typo3
 */
class FrontendUser extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
{
    /**
     * @return string
     */
    public function getFullName(): string
    {
        if (!empty($this->name)) {
            return $this->name;
        }

        if (empty($this->firstName) && empty($this->lastName)) {
            return $this->username;
        }

        if (!empty($this->firstName) && !empty($this->lastName)) {
            $spacer = ' ';
        }

        return $this->firstName . ($spacer ?? '') . $this->lastName;
    }
}
