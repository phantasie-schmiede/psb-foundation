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
use PSB\PsbFoundation\Utility\VariableUtility;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

/**
 * Class RequestParameterProvider
 *
 * @package PSB\PsbFoundation\Service\GlobalVariableProviders
 */
class RequestParameterProvider extends AbstractProvider
{
    /**
     * @param string $key
     * @param bool   $strict
     *
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public static function getRequestParameter(string $key, bool $strict = false)
    {
        return VariableUtility::getValueByPath(self::getRequestParameters(), $key, $strict);
    }

    /**
     * @TODO: Remove public access. Data should be retrieved via GlobalVariableService only!
     *
     * @return array
     * @throws ContainerExceptionInterface
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public static function getRequestParameters(): array
    {
        $parameters = GeneralUtility::_GET();
        ArrayUtility::mergeRecursiveWithOverrule($parameters, GeneralUtility::_POST());

        array_walk_recursive($parameters, static function (&$item) {
            $item = filter_var($item, FILTER_SANITIZE_STRING);
            $item = StringUtility::convertString($item);
        });

        return $parameters;
    }

    /**
     * @return array
     * @throws ContainerExceptionInterface
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function getGlobalVariables(): array
    {
        return self::getRequestParameters();
    }
}
