<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits\PropertyInjection;

use TYPO3\CMS\Core\Context\Context;

/**
 * Trait ContextTrait
 *
 * @package PSB\PsbFoundation\Traits\PropertyInjection
 */
trait ContextTrait
{
    /**
     * @var Context
     */
    protected Context $context;

    /**
     * @param Context $context
     */
    public function injectContext(Context $context): void
    {
        $this->context = $context;
    }
}
