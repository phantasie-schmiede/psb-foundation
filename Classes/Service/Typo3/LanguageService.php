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

namespace PSB\PsbFoundation\Service\Typo3;

use PSB\PsbFoundation\Service\LocalizationService;
use TYPO3\CMS\Core\Localization\LanguageService as Typo3LanguageServiceAlias;

/**
 * Class LanguageService
 *
 * Overwrites the original function in order to respect plural forms.
 * To hand over the correct index value in the clean way (as method parameter), you would have to override and copy(!)
 * a lot more functions that are used on the way here. Therefore, this is done by a static property.
 *
 * @package PSB\PsbFoundation\Service\Typo3
 * @TODO: Check original file on TYPO3 update!
 */
class LanguageService extends Typo3LanguageServiceAlias
{
    /**
     * Returns the label with key $index from the $LOCAL_LANG array used as the second argument respecting possible
     * plural forms (falls back to plural form 0 if other plural form is given but not defined in language file).
     *
     * @param string $index Label key
     * @param array $localLanguage $LOCAL_LANG array to get label key from
     * @return string
     */
    protected function getLLL(string $index, array $localLanguage): string
    {
        $pluralFormIndex = LocalizationService::$pluralFormIndex ?? 0;

        if (isset($localLanguage[$this->lang][$index])) {
            $value = is_string($localLanguage[$this->lang][$index])
                ? $localLanguage[$this->lang][$index]
                : ($localLanguage[$this->lang][$index][$pluralFormIndex]['target'] ?? $localLanguage[$this->lang][$index][0]['target']);
        } elseif (isset($localLanguage['default'][$index])) {
            $value = is_string($localLanguage['default'][$index])
                ? $localLanguage['default'][$index]
                : ($localLanguage['default'][$index][$pluralFormIndex]['target'] ?? $localLanguage['default'][$index][0]['target']);
        } else {
            $value = '';
        }

        return $value;
    }
}
