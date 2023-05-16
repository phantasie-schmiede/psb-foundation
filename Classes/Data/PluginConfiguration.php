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
     * @param bool        $ajaxCacheable
     * @param ContentType $ajaxContentType
     * @param bool        $ajaxDisableAllHeaderCode
     * @param int         $ajaxTypeNum
     * @param array       $controllers
     * @param string      $flexForm
     * @param string      $group
     * @param string      $iconIdentifier
     * @param string      $title
     */
    public function __construct(
        protected string      $name,
        protected bool        $addToElementWizard = true,
        protected bool        $ajaxCacheable = false,
        protected ContentType $ajaxContentType = ContentType::HTML,
        protected bool        $ajaxDisableAllHeaderCode = false,
        protected int         $ajaxTypeNum = 0,
        protected array       $controllers = [],
        protected string      $flexForm = '',
        protected string      $group = '',
        protected string      $iconIdentifier = '',
        protected string      $title = '',
    )
    {
    }

    /**
     * @return bool
     */
    public function isAddToElementWizard(): bool
    {
        return $this->addToElementWizard;
    }

    /**
     * @return ContentType
     */
    public function getAjaxContentType(): ContentType
    {
        return $this->ajaxContentType;
    }

    /**
     * @return int
     */
    public function getAjaxTypeNum(): int
    {
        return $this->ajaxTypeNum;
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
     * @return bool
     */
    public function isAjaxCacheable(): bool
    {
        return $this->ajaxCacheable;
    }

    /**
     * @return bool
     */
    public function isAjaxDisableAllHeaderCode(): bool
    {
        return $this->ajaxDisableAllHeaderCode;
    }
}
