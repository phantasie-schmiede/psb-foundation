<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\EventListener;

use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;

/**
 * Class DatabaseEnricher
 *
 * @package PSB\PsbFoundation\EventListener
 */
class DatabaseEnricher
{
    public function __invoke(AlterTableDefinitionStatementsEvent $event): void
    {
//        var_dump($event->getSqlData());
//        die();
    }
}
