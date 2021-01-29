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

namespace PSB\PsbFoundation\Php;

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
                && !StringUtility::beginsWith($line, 'use')
                && false !== mb_strpos($line, 'class ' . $this->getShortName())
            ) {
                // reached class declaration line, thus no more use-statements can occur
                return $namespaces;
            }

            if (StringUtility::beginsWith($line, 'use')
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
