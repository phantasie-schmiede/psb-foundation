<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\TypoScript;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

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
