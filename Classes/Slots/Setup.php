<?php
declare(strict_types=1);

namespace PSB\PsbFoundation\Slots;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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

use Exception;
use PSB\PsbFoundation\Data\ExtensionInformation;
use PSB\PsbFoundation\Service\DocComment\DocCommentParserService;
use PSB\PsbFoundation\Service\DocComment\ValueParsers\PluginActionParser;
use PSB\PsbFoundation\Service\DocComment\ValueParsers\PluginConfigParser;
use PSB\PsbFoundation\Service\DocComment\ValueParsers\TcaConfigParser;
use PSB\PsbFoundation\Service\DocComment\ValueParsers\TcaFieldConfigParser;
use PSB\PsbFoundation\Service\DocComment\ValueParsers\TcaMappingParser;
use PSB\PsbFoundation\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class Setup
 * @package PSB\PsbFoundation\Slots
 */
class Setup
{
    /**
     * @param string $extensionKey
     *
     * @throws Exception
     */
    public function onInstall(string $extensionKey): void
    {
        $extensionInformation = ObjectUtility::get(ExtensionInformation::class);

        if ($extensionInformation->getExtensionKey() !== $extensionKey) {
            return;
        }

        $docCommentParser = GeneralUtility::makeInstance(ObjectManager::class)->get(DocCommentParserService::class);
        $docCommentParser->addValueParser(PluginActionParser::ANNOTATION_TYPE, PluginActionParser::class,
            DocCommentParserService::VALUE_TYPES['MERGE']);
        $docCommentParser->addValueParser(PluginConfigParser::ANNOTATION_TYPE, PluginConfigParser::class,
            DocCommentParserService::VALUE_TYPES['MERGE']);
        $docCommentParser->addValueParser(TcaConfigParser::ANNOTATION_TYPE, TcaConfigParser::class,
            DocCommentParserService::VALUE_TYPES['MERGE']);
        $docCommentParser->addValueParser(TcaFieldConfigParser::ANNOTATION_TYPE, TcaFieldConfigParser::class,
            DocCommentParserService::VALUE_TYPES['MERGE']);
        $docCommentParser->addValueParser(TcaMappingParser::ANNOTATION_TYPE, TcaMappingParser::class,
            DocCommentParserService::VALUE_TYPES['MERGE']);
    }

    /**
     * @param string $extensionKey
     */
    public function onUninstall(string $extensionKey): void
    {
        $extensionInformation = ObjectUtility::get(ExtensionInformation::class);

        if ($extensionInformation->getExtensionKey() !== $extensionKey) {
            return;
        }

        $docCommentParser = GeneralUtility::makeInstance(ObjectManager::class)->get(DocCommentParserService::class);
        $docCommentParser->removeValueParsers([
            PluginActionParser::class,
            PluginConfigParser::class,
            TcaConfigParser::class,
            TcaFieldConfigParser::class,
            TcaMappingParser::class,
        ]);
    }
}
