<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Annotation\TCA\Column;

/**
 * Class Image
 *
 * @Annotation
 * @package PSB\PsbFoundation\Annotation\TCA\Column
 */
class Image extends AbstractFalColumnAnnotation
{
    public const USE_CONFIGURATION_FILE_TYPES = 'USE_CONFIGURATION_FILE_TYPES';

    /**
     * @var string
     */
    protected string $allowedFileTypes = self::USE_CONFIGURATION_FILE_TYPES;
}
