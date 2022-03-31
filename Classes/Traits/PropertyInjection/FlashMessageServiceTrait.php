<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits\PropertyInjection;

use TYPO3\CMS\Core\Messaging\FlashMessageService;

/**
 * Trait FlashMessageServiceTrait
 *
 * @package PSB\PsbFoundation\Traits\PropertyInjection
 */
trait FlashMessageServiceTrait
{
    /**
     * @var FlashMessageService
     */
    protected FlashMessageService $flashMessageService;

    /**
     * @param FlashMessageService $flashMessageService
     */
    public function injectFlashMessageService(FlashMessageService $flashMessageService): void
    {
        $this->flashMessageService = $flashMessageService;
    }
}
