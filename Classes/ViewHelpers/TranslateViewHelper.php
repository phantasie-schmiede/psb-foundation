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

namespace PSB\PsbFoundation\ViewHelpers;

use PSB\PsbFoundation\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class TranslateViewHelper
 *
 * Overwrites the core ViewHelper in order to use \PSB\PsbFoundation\Utility\LocalizationUtility which is able to log
 * missing translations.
 *
 * @package PSB\PsbFoundation\ViewHelpers
 */
class TranslateViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\TranslateViewHelper
{
    /**
     * Wrapper call to static LocalizationUtility
     *
     * @param string   $id                      Translation Key
     * @param string   $extensionName           UpperCamelCased extension key (for example BlogExample)
     * @param array    $arguments               Arguments to be replaced in the resulting string
     * @param string   $languageKey             Language key to use for this translation
     * @param string[] $alternativeLanguageKeys Alternative language keys if no translation does exist
     *
     * @return string|null
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected static function translate(
        $id,
        $extensionName,
        $arguments,
        $languageKey,
        $alternativeLanguageKeys
    ): ?string {
        return LocalizationUtility::translate($id, $extensionName, $arguments, $languageKey, $alternativeLanguageKeys);
    }
}
