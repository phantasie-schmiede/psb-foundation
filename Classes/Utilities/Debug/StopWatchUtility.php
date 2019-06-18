<?php
declare(strict_types=1);

namespace PSB\PsbFoundation\Utilities\Debug;

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

use TYPO3\CMS\Core\Utility\DebugUtility;

/**
 * Class StopWatchUtility
 * @package PSB\PsbFoundation\Utilities\Debug
 */
class StopWatchUtility
{
    /**
     * @var string
     */
    protected $header;

    /**
     * @var int
     */
    protected $precision;

    /**
     * @var double
     */
    protected $splitTime = 0.0;

    /**
     * @var double
     */
    protected $startTime = 0.0;

    /**
     * @var array
     */
    protected $timeLog = [];

    /**
     * StopwatchUtility constructor.
     *
     * @param string $header
     * @param int    $precision
     */
    public function __construct(string $header = 'StopWatch', int $precision = 4)
    {
        $this->setHeader($header);
        $this->setPrecision($precision);
    }

    /**
     * @return string
     */
    public function getHeader(): string
    {
        return $this->header;
    }

    /**
     * @param string $header
     */
    public function setHeader(string $header): void
    {
        $this->header = $header;
    }

    /**
     * @return int
     */
    public function getPrecision(): int
    {
        return $this->precision;
    }

    /**
     * @param int $precision
     */
    public function setPrecision(int $precision): void
    {
        $this->precision = $precision;
    }

    /**
     * @return float
     */
    public function getSplitTime(): float
    {
        return $this->splitTime;
    }

    /**
     * @param float $splitTime
     */
    public function setSplitTime(float $splitTime): void
    {
        $this->splitTime = $splitTime;
    }

    /**
     * @return float
     */
    public function getStartTime(): float
    {
        return $this->startTime;
    }

    /**
     * @param float $startTime
     */
    public function setStartTime(float $startTime): void
    {
        $this->startTime = $startTime;
    }

    /**
     * @param string $logEntry
     */
    public function addToTimeLog(string $logEntry): void
    {
        $this->timeLog[] = $logEntry;
    }

    /**
     * @return array
     */
    public function getTimeLog(): array
    {
        return $this->timeLog;
    }

    /**
     * @param array $timeLog
     */
    public function setTimeLog(array $timeLog): void
    {
        $this->timeLog = $timeLog;
    }

    public function reset(): void
    {
        $this->setSplitTime(0.0);
        $this->setStartTime(0.0);
    }

    /**
     * @param string $comment
     */
    public function split(string $comment = ''): void
    {
        $newSplitTime = microtime(true);

        if (0.0 === $this->getSplitTime()) {
            $this->setSplitTime($this->getStartTime());
        }

        $this->addLogEntry($newSplitTime, $comment);
        $this->setSplitTime($newSplitTime);
    }

    public function start(): void
    {
        if (0.0 !== $this->getStartTime()) {
            throw new \RuntimeException('StopWatch was still running. It has to be stopped or resetted in order to be started again!',
                1551096470);
        }

        $this->setStartTime(microtime(true));
    }

    /**
     * @param string $comment
     * @param bool   $noHtml
     */
    public function stop(string $comment = '', bool $noHtml = false): void
    {
        $this->addLogEntry(microtime(true), $comment);

        if ($noHtml) {
            print_r($this->getHeader().LF);
            print_r($this->getTimeLog());
            print_r(LF);
        } else {
            DebugUtility::debug($this->getTimeLog(), $this->getHeader());
        }
        $this->reset();
    }

    /**
     * @param float  $currentTime
     * @param string $comment
     */
    private function addLogEntry(float $currentTime, string $comment = ''): void
    {
        if (0.0 === $this->getSplitTime()) {
            $line = 'Total time: '.number_format($currentTime - $this->getStartTime(), $this->getPrecision()).' seconds';
        } else {
            $line = 'Split time: '.number_format($currentTime - $this->getSplitTime(),
                    $this->getPrecision()).' seconds, Total time: '.number_format($currentTime - $this->getStartTime(),
                    $this->getPrecision()).' seconds';
        }

        if ('' !== $comment) {
            $line .= ' // '.$comment;
        }

        $this->addToTimeLog($line);
    }
}
