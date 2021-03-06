<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Traits\PropertyInjection;

use TYPO3\CMS\Extbase\Property\PropertyMapper;

/**
 * Trait PropertyMapperTrait
 *
 * @package PSB\PsbFoundation\Traits\PropertyInjection
 */
trait PropertyMapperTrait
{
    /**
     * @var PropertyMapper
     */
    protected PropertyMapper $propertyMapper;

    /**
     * @param PropertyMapper $propertyMapper
     *
     * @return void
     */
    public function injectPropertyMapper(PropertyMapper $propertyMapper): void
    {
        $this->propertyMapper = $propertyMapper;
    }
}
