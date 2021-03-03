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

use PSB\PsbFoundation\Traits\PropertyInjection\StopWatchServiceTrait;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class StopWatchViewHelper
 *
 * @package PSB\PsbFoundation\ViewHelpers\Debug
 */
class StopWatchViewHelper extends AbstractViewHelper
{
    use StopWatchServiceTrait;

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
        $this->stopWatchService->setHeader($this->arguments['header']);
        $this->stopWatchService->setPrecision($this->arguments['precision']);

        $this->stopWatchService->start();
        $output = $this->renderChildren();
        $this->stopWatchService->stop();

        return $output;
    }
}
