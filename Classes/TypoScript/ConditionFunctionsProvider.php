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

namespace PSB\PsbFoundation\TypoScript;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ConditionFunctionsProvider
 *
 * @package PSB\PsbFoundation\TypoScript
 */
class ConditionFunctionsProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            $this->getExtensionLoadedCondition(),
            $this->getFileExistsCondition(),
        ];
    }

    /**
     * @return ExpressionFunction
     */
    protected function getExtensionLoadedCondition(): ExpressionFunction
    {
        return new ExpressionFunction('extensionLoaded', static function () {
            // Not implemented, we only use the evaluator
        }, static function ($variables, $extensionKey) {
            return ExtensionManagementUtility::isLoaded($extensionKey);
        });
    }

    /**
     * @return ExpressionFunction
     */
    protected function getFileExistsCondition(): ExpressionFunction
    {
        return new ExpressionFunction('fileExists', static function () {
            // Not implemented, we only use the evaluator
        }, static function ($variables, $filePath) {
            $filePath = GeneralUtility::getFileAbsFileName($filePath);

            return file_exists($filePath);
        });
    }
}
