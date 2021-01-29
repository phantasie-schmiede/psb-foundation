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

namespace PSB\PsbFoundation\ViewHelpers\Condition;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class ExtensionLoadedViewHelper
 *
 * @package PSB\PsbFoundation\ViewHelpers\Conditions
 */
class ExtensionLoadedViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('extensionKey', 'string', 'Key of the extension which shall be checked.', true);
    }

    /**
     * @return string
     */
    public function render(): string
    {
        if (ExtensionManagementUtility::isLoaded($this->arguments['extensionKey'])) {
            return $this->renderChildren();
        }

        return '';
    }
}
