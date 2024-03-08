<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Attribute\TCA;

use PSB\PsbFoundation\Attribute\AbstractAttribute;
use PSB\PsbFoundation\Service\Configuration\TcaService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractTcaAttribute
 *
 * @package PSB\PsbFoundation\Attribute\TCA
 */
abstract class AbstractTcaAttribute extends AbstractAttribute
{
    protected TcaService $tcaService;

    public function __construct()
    {
        $this->tcaService = GeneralUtility::makeInstance(TcaService::class);
    }
}
