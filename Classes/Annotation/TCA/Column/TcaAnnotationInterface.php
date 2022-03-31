<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Annotation\TCA\Column;

/**
 * Interface TcaAnnotationInterface
 *
 * @package PSB\PsbFoundation\Annotation\TCA\Column
 */
interface TcaAnnotationInterface
{
    /**
     * @param string $label
     */
    public function setLabel(string $label);

    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @return string
     */
    public function getPosition(): string;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return string
     */
    public function getTypeList(): string;
}
