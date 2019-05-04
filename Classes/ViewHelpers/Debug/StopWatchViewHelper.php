<?php
declare(strict_types=1);

namespace PS\PsFoundation\ViewHelpers\Debug;

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

use PS\PsFoundation\Traits\Injections\ObjectManagerTrait;
use PS\PsFoundation\Utilities\Debug\StopWatchUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class StopWatchViewHelper
 * @package PS\PsFoundation\ViewHelpers\Debug
 */
class StopWatchViewHelper extends AbstractViewHelper
{
    use ObjectManagerTrait;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('header', 'string', 'Header for the debug output', false, 'Stopwatch');
        $this->registerArgument('precision', 'integer', 'Number of decimals in output', false, 4);
    }

    /**
     * @return mixed
     */
    public function render()
    {
        $stopWatch = $this->getObjectManager()->get(StopWatchUtility::class, $this->arguments['header'],
            $this->arguments['precision']);
        $stopWatch->start();
        $output = $this->renderChildren();
        $stopWatch->stop();

        return $output;
    }
}
