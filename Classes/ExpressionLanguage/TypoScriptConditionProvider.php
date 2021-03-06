<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\ExpressionLanguage;

use PSB\PsbFoundation\TypoScript\ConditionFunctionsProvider;
use TYPO3\CMS\Core\ExpressionLanguage\AbstractProvider;

/**
 * Class TypoScriptConditionProvider
 *
 * @package PSB\PsbFoundation\ExpressionLanguage
 */
class TypoScriptConditionProvider extends AbstractProvider
{
    public function __construct()
    {
        $this->expressionLanguageProviders = [
            ConditionFunctionsProvider::class,
        ];
    }
}
