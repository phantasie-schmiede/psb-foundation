<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Data;

use PSB\PsbBudgetManager\Controller\Backend\ImportController;
use PSB\PsbFoundation\Controller\Backend\FrontendVariableController;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ExtensionInformation
 *
 * @package PSB\PsbFoundation\Data
 */
class ExtensionInformation extends AbstractExtensionInformation
{
    public function __construct()
    {
        parent::__construct();
        $mainModuleKey = $this->buildModuleKeyPrefix() . 'main';
        $this->addMainModule(
            GeneralUtility::makeInstance(
                MainModuleConfiguration::class,
                key     : $mainModuleKey,
                position: [
                    'after'  => 'file',
                    'before' => 'site',
                ],
            )
        );
        $this->addModule(
            GeneralUtility::makeInstance(
                ModuleConfiguration::class,
                controllers        : [FrontendVariableController::class],
                key                : $this->buildModuleKeyPrefix() . 'frontendVariables',
                navigationComponent: '@typo3/backend/page-tree/page-tree-element',
                parentModule       : $mainModuleKey,
            )
        );
    }
}
