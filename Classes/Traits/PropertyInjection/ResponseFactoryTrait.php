<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits\PropertyInjection;

use Psr\Http\Message\ResponseFactoryInterface;

/**
 * Trait ResponseFactoryInterfaceTrait
 *
 * @package PSB\PsbFoundation\Traits\PropertyInjection
 */
trait ResponseFactoryTrait
{
    /**
     * @var ResponseFactoryInterface
     */
    protected ResponseFactoryInterface $responseFactory;

    /**
     * @param ResponseFactoryInterface $responseFactory
     *
     * @return void
     */
    public function injectResponseFactory(ResponseFactoryInterface $responseFactory): void
    {
        $this->responseFactory = $responseFactory;
    }
}
