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

namespace PSB\PsbFoundation\Service\Configuration\ValueParsers;

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Interface ValueParserInterface
 *
 * Your parser class also has to define a constant named MARKER_TYPE (the part between the beginning "###" and ":").
 * Example: const MARKER_TYPE = 'EXAMPLE';
 * Usage: ###EXAMPLE:value###
 *
 * @package PSB\PsbFoundation\Service\Configuration\ValueParsers
 */
interface ValueParserInterface extends SingletonInterface
{
    /**
     * @param string|null $value the string between ':' and '###'
     *
     * @return mixed
     */
    public function processValue(?string $value);
}
