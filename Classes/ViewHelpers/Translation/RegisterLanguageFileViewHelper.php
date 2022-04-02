<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\ViewHelpers\Translation;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class RegisterLanguageFileViewHelper
 *
 * This ViewHelper allows you to define path aliases for language files inside your Fluid-template. Works with the
 * TranslateViewHelper of this extension only.
 *
 * Examples:
 * <psg:translation.registerLanguageFile file="EXT:my_extension/Resources/Private/Language/myFile.xlf"
 * name="myLanguageFile" />
 * <psg:translate id="myLanguageFile:myLabel" />
 *
 * If you omit the name attribute, the filename will be used as alias:
 * <psg:translation.registerLanguageFile file="EXT:my_extension/Resources/Private/Language/myFile.xlf" />
 * <psg:translate id="myFile:myLabel" />
 *
 * @package PSB\PsbFoundation\ViewHelpers\Translation
 */
class RegisterLanguageFileViewHelper extends AbstractViewHelper
{
    public const REGISTRY_KEY  = 'languageFileRegistry';
    public const VARIABLE_NAME = 'psbFoundation';

    /**
     * @return void
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('file', 'string', 'File path (supports EXT:) of the language file that should be used.', true,
            true);
        $this->registerArgument('name', 'string', 'Name to reference this file. If empty, the filename will be used.',
            false);
    }

    /**
     * @return void
     */
    public function render(): void
    {
        ['file' => $file, 'name' => $name] = $this->arguments;
        $templateVariableContainer = $this->renderingContext->getVariableProvider();

        if (empty($name)) {
            $name = pathinfo($file, PATHINFO_FILENAME);
        }

        $registry = [];

        if ($templateVariableContainer->exists(self::VARIABLE_NAME)) {
            $registry = $templateVariableContainer->get(self::VARIABLE_NAME);
        }

        $registry[self::REGISTRY_KEY][$name] = $file;

        $templateVariableContainer->add(self::VARIABLE_NAME, $registry);
    }
}
