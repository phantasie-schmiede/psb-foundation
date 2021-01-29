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

namespace PSB\PsbFoundation\Domain\Model;

use PSB\PsbFoundation\Service\DocComment\Annotations\TCA\Input;

/**
 * Class ModelWithName
 *
 * Adds a property "name" to your model.
 *
 * @package PSB\PsbFoundation\Domain\Model
 */
abstract class AbstractModelWithName extends AbstractModel
{
    /**
     * @var string
     * @Input()
     */
    protected string $name = '';

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
