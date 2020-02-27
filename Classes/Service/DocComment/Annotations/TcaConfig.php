<?php
declare(strict_types=1);
namespace PSB\PsbFoundation\Service\DocComment\Annotations;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019-2020 Daniel Ablass <dn@phantasie-schmiede.de>, PSbits
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class TcaConfig
 *
 * Use this in the annotations of your domain model properties. Possible attributes are all those listed in the
 * official TCA documentation: https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Columns/Index.html EXCEPT
 * "config". To define those values use the TcaFieldConfigParser annotation.
 *
 * @Annotation
 * @package PSB\PsbFoundation\Service\DocComment\Annotations
 */
class TcaConfig extends AbstractAnnotation
{
    /**
     * if set to true, \PSB\PsbFoundation\ViewHelpers\Form\BuildFromTcaViewHelper can be used for this domain model. If
     * used in class annotation, this attribute applies to all properties annotated with FieldConfig.
     * @TODO: But it can also be set for each property individually.
     *
     * @var bool
     */
    protected bool $editableInFrontend = false;

    /**
     * @var string
     */
    protected string $label = '';

    /**
     * @var bool
     */
    protected bool $labelALtForce = false;

    /**
     * @var string
     */
    protected string $labelAlt = '';

    /**
     * @var string
     */
    protected string $searchFields = '';

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getLabelAlt(): string
    {
        return $this->labelAlt;
    }

    /**
     * @param string $labelAlt
     */
    public function setLabelAlt(string $labelAlt): void
    {
        $this->labelAlt = $labelAlt;
    }

    /**
     * @return string
     */
    public function getSearchFields(): string
    {
        return $this->searchFields;
    }

    /**
     * @param string $searchFields
     */
    public function setSearchFields(string $searchFields): void
    {
        $this->searchFields = $searchFields;
    }

    /**
     * @return bool
     */
    public function isEditableInFrontend(): bool
    {
        return $this->editableInFrontend;
    }

    /**
     * @param bool $editableInFrontend
     */
    public function setEditableInFrontend(bool $editableInFrontend): void
    {
        $this->editableInFrontend = $editableInFrontend;
    }

    /**
     * @return bool
     */
    public function isLabelALtForce(): bool
    {
        return $this->labelALtForce;
    }

    /**
     * @param bool $labelALtForce
     */
    public function setLabelALtForce(bool $labelALtForce): void
    {
        $this->labelALtForce = $labelALtForce;
    }
}
