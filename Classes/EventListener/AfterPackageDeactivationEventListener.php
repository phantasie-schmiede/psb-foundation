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

namespace PSB\PsbFoundation\EventListener;

use PSB\PsbFoundation\Traits\PropertyInjection\ExtensionInformationServiceTrait;
use TYPO3\CMS\Core\Package\Event\AfterPackageDeactivationEvent;

/**
 * Class AfterPackageDeactivationEventListener
 *
 * @package PSB\PsbFoundation\EventListener
 */
class AfterPackageDeactivationEventListener
{
    use ExtensionInformationServiceTrait;

    public function __invoke(AfterPackageDeactivationEvent $event): void
    {
        $this->extensionInformationService->deregister($event->getPackageKey());
    }
}
