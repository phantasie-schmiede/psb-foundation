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

use PSB\PsbFoundation\Exceptions\AnnotationException;
use PSB\PsbFoundation\Utility\ExtensionInformationUtility;
use PSB\PsbFoundation\Utility\StringUtility;
use ReflectionException;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Security\Exception\InvalidArgumentForHashGenerationException;

/**
 * Class TcaFieldConfig
 *
 * Use this in the annotations of your domain model properties. Possible attributes are all those listed in the
 * official TCA documentation (depends on the type you defined with the TcaConfigParser annotation):
 * https://docs.typo3.org/m/typo3/reference-tca/master/en-us/ColumnsConfig/Index.html#columns-types
 *
 * @Annotation
 * @package PSB\PsbFoundation\Service\DocComment\Annotations
 */
class TcaFieldConfig extends AbstractAnnotation implements PreProcessorInterface
{
    /**
     * @var bool|null
     */
    protected ?bool $enableRichtext = null;

    /**
     * @var string|null
     */
    protected ?string $eval = null;

    /**
     * @var string|null
     */
    protected ?string $foreignField = null;

    /**
     * @var string|null
     */
    protected ?string $foreignTable = null;

    /**
     * @var array|null
     */
    protected ?array $items = null;

    /**
     * @var string|null
     */
    protected ?string $linkedModel = null;

    /**
     * @var string|null
     */
    protected ?string $mm = null;

    /**
     * @var string|null
     */
    protected ?string $mmOppositeField = null;

    /**
     * @var string|null
     */
    protected ?string $type = null;

    /**
     * TcaFieldConfig constructor.
     *
     * @param array $data
     *
     * @throws AnnotationException
     * @throws Exception
     * @throws InvalidArgumentForHashGenerationException
     * @throws ReflectionException
     * @throws \Exception
     */
    public function __construct(array $data)
    {
        // instead of directly specifying a foreign table, it is possible to specify a domain model class instead
        if (isset ($data['linkedModel']) && class_exists($data['linkedModel'])) {
            $data['foreignTable'] = ExtensionInformationUtility::convertClassNameToTableName($data['linkedModel']);
            unset($data['linkedModel']);
        }

        //            if (isset ($data['items']) && is_array($data['items'])) {
        //                // transform associative array to simple array for TCA
        //                $data['items'] = array_map(static function ($key, $value) {
        //                    return [ucwords(str_replace('_', ' ', mb_strtolower($key))), $value];
        //                }, array_keys($data['items']), array_values($data['items']));
        //            }

        parent::__construct($data);
    }

    /**
     * @return bool|null
     */
    public function getEnableRichtext(): ?bool
    {
        return $this->enableRichtext;
    }

    /**
     * @param bool|null $enableRichtext
     */
    public function setEnableRichtext(?bool $enableRichtext): void
    {
        $this->enableRichtext = $enableRichtext;
    }

    /**
     * @return string|null
     */
    public function getEval(): ?string
    {
        return $this->eval;
    }

    /**
     * @param string|null $eval
     */
    public function setEval(?string $eval): void
    {
        $this->eval = $eval;
    }

    /**
     * @return string|null
     * @throws AnnotationException
     * @throws Exception
     * @throws InvalidArgumentForHashGenerationException
     * @throws ReflectionException
     */
    public function getForeignField(): ?string
    {
        if (null === $this->foreignField) {
            return null;
        }

        return ExtensionInformationUtility::convertPropertyNameToColumnName($this->foreignField);
    }

    /**
     * @param string|null $foreignField
     */
    public function setForeignField(?string $foreignField): void
    {
        $this->foreignField = $foreignField;
    }

    /**
     * @return string|null
     */
    public function getForeignTable(): ?string
    {
        return $this->foreignTable;
    }

    /**
     * @param string|null $foreignTable
     */
    public function setForeignTable(?string $foreignTable): void
    {
        $this->foreignTable = $foreignTable;
    }

    /**
     * @return array|null
     */
    public function getItems(): ?array
    {
        return $this->items;
    }

    /**
     * @param array|null $items
     */
    public function setItems(?array $items): void
    {
        $this->items = $items;
    }

    /**
     * @return string|null
     */
    public function getLinkedModel(): ?string
    {
        return $this->linkedModel;
    }

    /**
     * @param string|null $linkedModel
     */
    public function setLinkedModel(?string $linkedModel): void
    {
        $this->linkedModel = $linkedModel;
    }

    /**
     * @return string|null
     */
    public function getMm(): ?string
    {
        return $this->mm;
    }

    /**
     * @param string|null $mm
     */
    public function setMm(?string $mm): void
    {
        $this->mm = $mm;
    }

    /**
     * @return string|null
     * @throws AnnotationException
     * @throws Exception
     * @throws InvalidArgumentForHashGenerationException
     * @throws ReflectionException
     */
    public function getMmOppositeField(): ?string
    {
        if (null === $this->mmOppositeField) {
            return null;
        }

        return ExtensionInformationUtility::convertPropertyNameToColumnName($this->mmOppositeField);
    }

    /**
     * @param string|null $mmOppositeField
     */
    public function setMmOppositeField(?string $mmOppositeField): void
    {
        $this->mmOppositeField = $mmOppositeField;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    /**
     * @param array $value
     *
     * @return array
     */
    public static function processProperties(array $value): array
    {
        if (isset($value['linkedModel']) && !StringUtility::endsWith($value['linkedModel'], '::class')) {
            $value['linkedModel'] .= '::class';
        }

        return $value;
    }
}
