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

namespace PSB\PsbFoundation\Service\GlobalVariableProviders;

/**
 * Class AbstractProvider
 *
 * @package PSB\PsbFoundation\Service\GlobalVariableProviders
 */
abstract class AbstractProvider implements GlobalVariableProviderInterface
{
    /**
     * @var bool
     */
    protected bool $cacheable = true;

    /**
     * Overwrite this method in extending class!
     *
     * @return bool|null
     */
    public static function isAvailable(): ?bool
    {
        return false;
    }

    /**
     * Overwrite this method in extending class!
     *
     * @return array
     */
    public function getGlobalVariables(): array
    {
        // TODO: Implement getGlobalVariables() method.
        return  [];
    }

    /**
     * When returned data isn't supposed to change anymore, set function's return value to true.
     *
     * @return bool
     */
    public function isCacheable(): bool
    {
        return $this->cacheable;
    }
}
