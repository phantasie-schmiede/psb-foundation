<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
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
