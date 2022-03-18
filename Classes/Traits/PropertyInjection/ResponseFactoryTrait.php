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
     */
    public function injectResponseFactory(ResponseFactoryInterface $responseFactory): void
    {
        $this->responseFactory = $responseFactory;
    }
}
