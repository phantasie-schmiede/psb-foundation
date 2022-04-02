<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits\PropertyInjection;

use TYPO3\CMS\Extbase\Persistence\ClassesConfigurationFactory;

/**
 * Trait ClassesConfigurationFactoryTrait
 *
 * @package PSB\PsbFoundation\Traits\PropertyInjection
 */
trait ClassesConfigurationFactoryTrait
{
    /**
     * @var ClassesConfigurationFactory
     */
    protected ClassesConfigurationFactory $classesConfigurationFactory;

    /**
     * @param ClassesConfigurationFactory $classesConfigurationFactory
     *
     * @return void
     */
    public function injectClassesConfigurationFactory(ClassesConfigurationFactory $classesConfigurationFactory): void
    {
        $this->classesConfigurationFactory = $classesConfigurationFactory;
    }
}
