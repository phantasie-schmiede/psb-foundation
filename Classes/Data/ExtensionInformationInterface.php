<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Data;

/**
 * Interface ExtensionInformationInterface
 * @package PSB\PsbFoundation\Data
 */
interface ExtensionInformationInterface
{
    /**
     * @return string
     */
    public function getExtensionKey(): string;

    /**
     * @return string
     */
    public function getExtensionName(): string;

    /**
     * @return array
     */
    public function getMainModules(): array;

    /**
     * @return array
     */
    public function getModules(): array;

    /**
     * @return array
     */
    public function getPlugins(): array;

    /**
     * @return string
     */
    public function getVendorName(): string;
}
