<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits\PropertyInjection;

use PSB\PsbFoundation\Service\Configuration\TcaService;

/**
 * Trait TcaServiceTrait
 *
 * @package PSB\PsbFoundation\Traits\PropertyInjection
 */
trait TcaServiceTrait
{
    /**
     * @var TcaService|null
     */
    protected ?TcaService $tcaService = null;

    /**
     * @param TcaService $tcaService
     *
     * @return void
     */
    public function injectTcaService(TcaService $tcaService): void
    {
        $this->tcaService = $tcaService;
    }
}
