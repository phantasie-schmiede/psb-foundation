<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Data;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019-2020 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
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

/**
 * Interface ExtensionInformationInterface
 * @package PSB\PsbFoundation\Data
 */
interface ExtensionInformationInterface
{
    /**
     * If defined as true, language files of this extension will be editable in the Backend via the Language file
     * editor module. You can't override this constant if you implement this interface directly, but you can achieve
     * this when extending the AbstractExtensionInformation class (which you should definitely do). Another way to
     * allow the editing on a per file or even trans-unit basis is to include this attribute inside the specific tags
     * of your xlf-files: allow_language_file_editing="true"
     *
     * Example 1:
     * <?xml encoding="utf-8" standalone="yes" version="1.0" ?>
     * <xliff version="1.0">
     *     <file allow_language_file_editing="true" datatype="plaintext" date="2020-01-17T12:30:00+01:00"
     *     original="messages" product-name="psb_foundation" source-language="en">
     *         <header />
     *         <body>
     *             <trans-unit id="example1">
     *                 <source>editable</source>
     *             </trans-unit>
     *             <trans-unit id="example2">
     *                 <source>editable</source>
     *             </trans-unit>
     *         </body>
     *     </file>
     * </xliff>
     *
     * Example 2:
     * <?xml encoding="utf-8" standalone="yes" version="1.0" ?>
     * <xliff version="1.0">
     *     <file datatype="plaintext" date="2020-01-17T12:30:00+01:00" original="messages"
     *     product-name="psb_foundation" source-language="en">
     *         <header />
     *         <body>
     *             <trans-unit id="example1">
     *                 <source>not editable</source>
     *             </trans-unit>
     *             <trans-unit allow_language_file_editing="true" id="example2">
     *                 <source>editable</source>
     *             </trans-unit>
     *         </body>
     *     </file>
     * </xliff>
     */
    public const ALLOW_LANGUAGE_FILE_EDITING = false;
    public const ALLOW_LANGUAGE_FILE_EDITING_ATTRIBUTE_NAME = 'allow_language_file_editing';

    /**
     * @return string
     */
    public function getExtensionKey(): string;

    /**
     * @return string
     */
    public function getExtensionName(): string;

    /**
     * @return array
     */
    public function getModules(): array;

    /**
     * @return array
     */
    public function getPlugins(): array;

    /**
     * @return string
     */
    public function getVendorName(): string;
}
