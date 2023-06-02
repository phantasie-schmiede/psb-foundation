<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Service\GlobalVariableProviders;

use JsonException;
use PSB\PsbFoundation\Utility\StringUtility;
use PSB\PsbFoundation\Utility\VariableUtility;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
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
    public static function getRequestParameter(string $key, bool $strict = false): mixed
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
        $parameters = $_GET ?? [];
        ArrayUtility::mergeRecursiveWithOverrule($parameters, $_POST ?? []);

        array_walk_recursive($parameters, static function (&$item) {
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
