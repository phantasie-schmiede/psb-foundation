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

use JsonException;
use PSB\PsbFoundation\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class RequestParameterProvider
 *
 * @package PSB\PsbFoundation\Service\GlobalVariableProviders
 */
class RequestParameterProvider implements GlobalVariableProviderInterface
{
    /**
     * @var bool
     */
    protected bool $cacheable = false;

    /**
     * @return bool
     */
    public static function isAvailableDuringBootProcess(): bool
    {
        return true;
    }

    /**
     * @return array
     * @throws JsonException
     */
    public function getGlobalVariables(): array
    {
        $parameters = GeneralUtility::_GET();
        ArrayUtility::mergeRecursiveWithOverrule($parameters, GeneralUtility::_POST());

        array_walk_recursive($parameters, static function (&$item) {
            $item = filter_var($item, FILTER_SANITIZE_STRING);
            $item = StringUtility::convertString($item);
        });

        $this->setCacheable(true);

        return ['parameters' => $parameters];
    }

    /**
     * This must return false on first call. Otherwise the function getGlobalVariables() will never be called. When
     * returned data isn't supposed to change anymore, set function's return value to true.
     *
     * @return bool
     */
    public function isCacheable(): bool
    {
        return $this->cacheable;
    }

    /**
     * @param bool $cacheable
     */
    public function setCacheable(bool $cacheable): void
    {
        $this->cacheable = $cacheable;
    }
}
