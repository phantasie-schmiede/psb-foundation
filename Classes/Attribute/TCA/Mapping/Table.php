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
 * Class Table
 *
 * Use this attribute to map a domain model to a table which does not convey to the naming convention, e. g. when
 * extending another model.
 *
 * @package PSB\PsbFoundation\Attribute\TCA\Mapping
 * @see     https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ExtensionArchitecture/Extbase/Reference/Domain/Persistence.html#use-arbitrary-database-tables-with-an-extbase-model
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Table
{
    /**
     * @param string      $name
     * @param string|null $parentClass
     * @param string|null $recordType
     */
    public function __construct(
        protected string $name,
        protected string|null $parentClass = null,
        protected string|null $recordType = null,
    ) {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getParentClass(): ?string
    {
        return $this->parentClass;
    }

    /**
     * @return string|null
     */
    public function getRecordType(): ?string
    {
        return $this->recordType;
    }
}
