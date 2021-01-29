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

namespace PSB\PsbFoundation\Service\DocComment\Annotations\TCA;

use PSB\PsbFoundation\Service\Configuration\Fields;

/**
 * Class FloatingPoint
 *
 * @Annotation
 * @package PSB\PsbFoundation\Service\DocComment\Annotations\TCA
 */
class FloatingPoint extends Input
{
    public const TYPE = Fields::FIELD_TYPES['FLOAT'];

    /**
     * @var string
     */
    protected string $eval = 'double2';
}
