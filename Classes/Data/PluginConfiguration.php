<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Data;

use PSB\PsbFoundation\Enum\ContentType;

/**
 * Class PluginConfiguration
 *
 * @package PSB\PsbFoundation\Data
 */
class PluginConfiguration
{
    /**
     * @param string      $name
     * @param bool        $addToElementWizard
     * @param array       $controllers
     * @param string      $flexForm
     * @param string      $group
     * @param string      $iconIdentifier
     * @param string      $title
     * @param int         $typeNum
     * @param bool        $typeNumCacheable
     * @param ContentType $typeNumContentType
     * @param bool        $typeNumDisableAllHeaderCode
     */
    public function __construct(
        protected string      $name,
        protected bool        $addToElementWizard = true,
        protected array       $controllers = [],
        protected string      $flexForm = '',
        protected string      $group = '',
        protected string      $iconIdentifier = '',
        protected string      $title = '',
        protected int         $typeNum = 0,
        protected bool        $typeNumCacheable = false,
        protected ContentType $typeNumContentType = ContentType::HTML,
        protected bool        $typeNumDisableAllHeaderCode = true,
    ) {
    }

    /**
     * @return array
     */
    public function getControllers(): array
    {
        return $this->controllers;
    }

    /**
     * @return string
     */
    public function getFlexForm(): string
    {
        return $this->flexForm;
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @return string
     */
    public function getIconIdentifier(): string
    {
        return $this->iconIdentifier;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return int
     */
    public function getTypeNum(): int
    {
        return $this->typeNum;
    }

    /**
     * @return ContentType
     */
    public function getTypeNumContentType(): ContentType
    {
        return $this->typeNumContentType;
    }

    /**
     * @return bool
     */
    public function isAddToElementWizard(): bool
    {
        return $this->addToElementWizard;
    }

    /**
     * @return bool
     */
    public function isTypeNumCacheable(): bool
    {
        return $this->typeNumCacheable;
    }

    /**
     * @return bool
     */
    public function isTypeNumDisableAllHeaderCode(): bool
    {
        return $this->typeNumDisableAllHeaderCode;
    }
}
