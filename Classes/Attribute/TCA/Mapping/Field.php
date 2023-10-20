<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Attribute\TCA\Mapping;

use Attribute;

/**
 * Class Field
 *
 * Use this attribute to map a domain model property to a databse field name which does not convey to the naming
 * convention, e. g. when extending another model.
 *
 * @package PSB\PsbFoundation\Attribute\TCA\Mapping
 * @see     https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ExtensionArchitecture/Extbase/Reference/Domain/Persistence.html#use-arbitrary-database-tables-with-an-extbase-model
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Field
{
    /**
     * @param string $name
     */
    public function __construct(
        protected string $name,
    ) {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
