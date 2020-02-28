<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Php;

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

use PSB\PsbFoundation\Utility\StringUtility;
use ReflectionClass;
use RuntimeException;

/**
 * Class ExtendedReflectionClass
 * @package PSB\PsbFoundation\Php
 */
class ExtendedReflectionClass extends ReflectionClass
{
    /**
     * This function collects and returns all imported namespaces of the reflected class. No support for nested
     * use-statements yet!
     *
     * @return array
     */
    public function getImportedNamespaces(): array
    {
        $namespaces = [];
        $fileContents = file_get_contents($this->getFileName());
        $lines = StringUtility::explodeByLineBreaks($fileContents);
        array_map('trim', $lines);

        foreach ($lines as $line) {
            if (!$this->isComment($line)
                && !StringUtility::startsWith($line, 'use')
                && false !== mb_strpos($line, 'class ' . $this->getShortName())
            ) {
                // reached class declaration line, thus no more use-statements can occur
                return $namespaces;
            }

            if (StringUtility::startsWith($line, 'use')
                && StringUtility::endsWith($line, ';')
            ) {
                $namespace = mb_substr($line, 4, -1);

                if (false !== mb_strpos($line, ' as ')) {
                    [$namespace, $alias] = explode(' as ', $namespace);
                } else {
                    $namespaceParts = explode('\\', $namespace);
                    $alias = array_pop($namespaceParts);
                }

                $namespaces[$alias] = $namespace;
            }
        }

        throw new RuntimeException(__CLASS__ . ': ' . $this->getFileName() . ' has no detectable class declaration!',
            1582705645);
    }

    /**
     * @param string $line
     *
     * @return bool
     */
    private function isComment(string $line): bool
    {
        return (false !== mb_strpos($line, '*') || false !== mb_strpos($line, '/'));
    }
}
