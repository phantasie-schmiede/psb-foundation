<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Utility;

use Exception;
use JsonException;
use NumberFormatter;
use PSB\PsbFoundation\Service\TypoScriptProviderService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

/**
 * Class StringUtility
 *
 * @package PSB\PsbFoundation\Utility
 */
class StringUtility
{
    /**
     * mb_strrpos() is used for performance reasons here. mb_strpos() would search the whole string if the needle isn't
     * found right at the beginning. The combination of a reversed search and the negative offset ensure that only the
     * relevant part of the string is searched. Additionally mb_strrpos has a better performance than mb_substr().
     *
     * @TODO: Replace with str_starts_with() when switching to php 8!
     *
     * @param string $string
     * @param string $beginning
     *
     * @return bool
     */
    public static function beginsWith(string $string, string $beginning): bool
    {
        return 0 === mb_strrpos($string, $beginning, -mb_strlen($string));
    }

    /**
     * @param $url
     *
     * @return string
     */
    public static function cleanUrl($url): string
    {
        return html_entity_decode(urldecode($url));
    }

    /**
     * @param string|null $variable
     * @param bool        $convertEmptyStringToNull
     * @param array       $namespaces
     *
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public static function convertString(
        ?string $variable,
        bool $convertEmptyStringToNull = false,
        array $namespaces = []
    ) {
        if (null === $variable || ($convertEmptyStringToNull && '' === $variable)) {
            return null;
        }

        if ('' === $variable || false !== mb_strpos($variable, '{#')) {
            // string is either empty or contains quoted query parameter
            return $variable;
        }

        if (1 === strlen($variable) || !self::beginsWith($variable, '0') || in_array($variable[1], ['.', ','], true)) {
            $intRepresentation = filter_var($variable, FILTER_VALIDATE_INT);

            if (false !== $intRepresentation) {
                return $intRepresentation;
            }

            $floatRepresentation = filter_var(str_replace(',', '.', $variable), FILTER_VALIDATE_FLOAT);

            if (false !== $floatRepresentation) {
                return $floatRepresentation;
            }
        }

        if (self::beginsWith($variable, 'TS:') && !ContextUtility::isBootProcessRunning()) {
            $typoscriptProviderService = GeneralUtility::makeInstance(TypoScriptProviderService::class);
            [, $path] = GeneralUtility::trimExplode(':', $variable);

            if ($typoscriptProviderService->has($path)) {
                return $typoscriptProviderService->get($path);
            }
        }

        // check for constant
        if (0 < mb_strpos($variable, '::')) {
            [$className, $constantName] = GeneralUtility::trimExplode('::', $variable, true, 2);
            $className = ObjectUtility::getFullQualifiedClassName($className, $namespaces);

            // If $className is false, we have a false positive. It's not a constant, but for example CSS.
            if (false !== $className) {
                if ('class' === $constantName) {
                    return $className;
                }

                $variable = $className . '::' . $constantName;

                /*
                 * find all [...] segments after constant name and convert each one separately before trying to access that
                 * array path.
                 */
                if (0 < preg_match_all('/\[\'?(.*)\'?(](?=\[)|]$)/U', $constantName, $pathSegments)) {
                    $pathSegments = array_map(static function ($value) use ($convertEmptyStringToNull, $namespaces) {
                        return self::convertString(trim($value, '\'"'), $convertEmptyStringToNull, $namespaces);
                    }, $pathSegments[1]);

                    // get constant array (path information is stripped away)
                    $variable = constant(preg_replace('/\[\'?(.*)\'?]/', '', $variable));

                    try {
                        // now try to access the array path
                        return ArrayUtility::getValueByPath($variable, $pathSegments);
                    } catch (Exception $exception) {
                        throw new RuntimeException('Path "[' . implode('][',
                                $pathSegments) . ']" does not exist in array!',
                            1548170593);
                    }
                }

                // check for dot-notation of array path
                if (false !== mb_strpos($constantName, '.')) {
                    $pathSegments = explode('.', $constantName);
                    $pathSegments = array_map(static function ($value) use ($convertEmptyStringToNull, $namespaces) {
                        return self::convertString(trim($value, '\'"'), $convertEmptyStringToNull, $namespaces);
                    }, $pathSegments);

                    // remove constant name from array
                    array_shift($pathSegments);

                    $variable = constant(mb_substr($variable, 0, mb_strpos($variable, '.')));

                    try {
                        // now try to access the array path
                        return ArrayUtility::getValueByPath($variable, $pathSegments);
                    } catch (Exception $exception) {
                        throw new RuntimeException('Path "' . implode('.',
                                $pathSegments) . '" does not exist in array!',
                            1589385393);
                    }
                }

                return constant($variable);
            }
        }

        // check for JSON
        if (in_array($variable[0], ['{', '['], true)) {
            try {
                $decodedString = json_decode(str_replace('\'', '"', $variable), true, 512, JSON_THROW_ON_ERROR);

                if (null !== $decodedString) {
                    return $decodedString;
                }
            } catch (Exception $exception) {
                // The string is not valid JSON. Just continue.
            }
        }

        switch ($variable) {
            case 'true':
                return true;
            case 'false':
                return false;
            default:
                return $variable;
        }
    }

    /**
     * @param string $variable
     *
     * @return float
     */
    public static function convertToFloat(string $variable): float
    {
        return (float)str_replace(',', '.', $variable);
    }

    /**
     * @param string $string
     * @param int    $length
     * @param string $appendix
     * @param bool   $respectWordBoundaries
     * @param bool   $respectHtml Increases length of output string until all opened tags are properly closed
     *
     * @return string
     */
    public static function crop(
        string $string,
        int $length,
        string $appendix = '…',
        bool $respectWordBoundaries = true,
        bool $respectHtml = true
    ): string {
        if (mb_strlen($string) <= $length) {
            return $string;
        }

        $lastCharacterBeforeTruncation = '';

        if (true === $respectHtml) {
            $preparedString = preg_replace_callback('/<.*>/U', static function ($matches) {
                return '###TAG###' . $matches[0] . '###TAG###';
            }, $string);

            $stringParts = array_filter(explode('###TAG###', $preparedString));
            $openedTags = [];
            $pureTextLength = 0;
            $outputString = '';

            foreach ($stringParts as $stringPart) {
                if ('/>' !== mb_substr($stringPart, -2)) {
                    if (0 === mb_strpos($stringPart, '</')) {
                        $lastOpenedTag = array_pop($openedTags);
                        preg_match('/<\/(.+)>/U', $stringPart, $matches);
                        $closedTag = $matches[1];

                        if ($lastOpenedTag !== $closedTag) {
                            throw new RuntimeException(__CLASS__ . ': HTML tags in the input string are not properly nested.',
                                1565696694);
                        }
                    } elseif (0 === mb_strpos($stringPart, '<')) {
                        // extract the tag name
                        preg_match('/<(.+)[\s>]/U', $stringPart, $matches);
                        $openedTags[] = $matches[1];
                    } else {
                        if (empty($openedTags)) {
                            $stringPart = mb_substr($stringPart, 0, $length - $pureTextLength);
                        }

                        $lastCharacterBeforeTruncation = mb_substr($stringPart, -1);
                        $pureTextLength += mb_strlen($stringPart);
                    }
                }

                $outputString .= $stringPart;

                if (empty($openedTags) && $pureTextLength >= $length) {
                    $length = mb_strlen($outputString);
                    break;
                }
            }
        }

        if (true === $respectWordBoundaries) {
            $notMultiByteLength = strlen(mb_substr($string, 0, $length));
            preg_match('/[\n|\s]/', $string, $matches, 0, $notMultiByteLength);

            if (!empty($matches)) {
                $length = mb_strpos($string, $matches[0], $length);
            }

            $lastCharacterBeforeTruncation = mb_substr($string, $length - 1, 1);
        }

        if (in_array($lastCharacterBeforeTruncation, ['.', '!', '?'], true)) {
            $appendix = '';
        }

        return mb_substr($string, 0, $length) . $appendix;
    }

    /**
     * @TODO: Replace with str_ends_with() when switching to php 8!
     *
     * @param string $string
     * @param string $ending
     *
     * @return bool
     */
    public static function endsWith(string $string, string $ending): bool
    {
        $offset = mb_strlen($ending);

        if ($offset > mb_strlen($string)) {
            return false;
        }

        return mb_strpos($string, $ending, -$offset) === mb_strlen($string) - $offset;
    }

    /**
     * @param string $string
     *
     * @return array[]|false|string[]
     */
    public static function explodeByLineBreaks(string $string)
    {
        return preg_split('/' . implode('|', [CRLF, LF, CR]) . '/', $string);
    }

    /**
     * @param int $style
     *
     * @return NumberFormatter
     * @throws AspectNotFoundException
     */
    public static function getNumberFormatter(int $style = NumberFormatter::DEFAULT_STYLE): NumberFormatter
    {
        return NumberFormatter::create(ContextUtility::getCurrentLocale(), $style);
    }

    /**
     * @param string $propertyName
     *
     * @return string
     */
    public static function sanitizePropertyName(string $propertyName): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', strtolower($propertyName)))));
    }
}
