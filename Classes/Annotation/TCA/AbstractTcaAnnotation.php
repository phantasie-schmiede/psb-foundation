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

namespace PSB\PsbFoundation\Annotation\TCA;

use Exception;
use PSB\PsbFoundation\Annotation\AbstractAnnotation;
use PSB\PsbFoundation\Service\Configuration\TcaService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AbstractTcaAnnotation
 *
 * @package PSB\PsbFoundation\Annotation\TCA
 */
abstract class AbstractTcaAnnotation extends AbstractAnnotation
{
    /**
     * @var TcaService
     */
    protected TcaService $tcaService;

    /**
     * @param array $data
     *
     * @throws Exception
     */
    public function __construct(array $data = [])
    {
        $this->tcaService = GeneralUtility::makeInstance(TcaService::class);
        parent::__construct($data);
    }
}
