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

namespace PSB\PsbFoundation\Views;

use PSB\PsbFoundation\Service\GlobalVariableService;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class TemplateView
 * @package PSB\PsbFoundation\Views
 */
class TemplateView extends \TYPO3\CMS\Fluid\View\TemplateView
{
    public function initializeView(): void
    {
        parent::initializeView();
        $this->assignMultiple(GlobalVariableService::get());
    }

    /**
     * @param string  $partialName
     * @param string  $sectionName
     * @param array   $variables
     * @param boolean $ignoreUnknown Ignore an unknown section and just return an empty string
     *
     * @return string
     * @throws Exception
     */
    public function renderPartial($partialName, $sectionName, array $variables, $ignoreUnknown = false): string
    {
        $globalVariables = GlobalVariableService::get();

        foreach ($globalVariables as $key => $value) {
            if (!isset($variables[$key])) {
                $variables[$key] = $value;
            }
        }

        return parent::renderPartial($partialName, $sectionName, $variables, $ignoreUnknown);
    }
}
