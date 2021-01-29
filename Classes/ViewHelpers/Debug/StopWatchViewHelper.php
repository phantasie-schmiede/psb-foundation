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

namespace PSB\PsbFoundation\ViewHelpers\Debug;

use PSB\PsbFoundation\Traits\InjectionTrait;
use PSB\PsbFoundation\Utility\Debug\StopWatchUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class StopWatchViewHelper
 *
 * @package PSB\PsbFoundation\ViewHelpers\Debug
 */
class StopWatchViewHelper extends AbstractViewHelper
{
    use InjectionTrait;

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
     * @throws Exception
     */
    public function render()
    {
        $stopWatch = $this->get(StopWatchUtility::class, $this->arguments['header'],
            $this->arguments['precision']);
        $stopWatch->start();
        $output = $this->renderChildren();
        $stopWatch->stop();

        return $output;
    }
}
