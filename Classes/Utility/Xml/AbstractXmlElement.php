<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Utility\Xml;

use PSB\PsbFoundation\Traits\AutoFillPropertiesTrait;
use PSB\PsbFoundation\Traits\PropertyInjection\ObjectServiceTrait;
use PSB\PsbFoundation\Utility\ObjectUtility;
use ReflectionClass;
use ReflectionException;

/**
 * Class AbstractXmlElement
 *
 * @package PSB\PsbFoundation\Utility\Xml
 */
class AbstractXmlElement implements XmlElementInterface
{
    use AutoFillPropertiesTrait, ObjectServiceTrait;

    /**
     * @var array
     */
    protected array $_attributes = [];

    /**
     * @var array Should be of type mixed, but Extbase throws an error in that case. Thus, we have to use this
     *            workaround.
     */
    protected array $_nodeValue = [];

    /**
     * @var int|null
     */
    protected ?int $_position = null;

    /**
     * @param array $childData
     *
     * @throws ReflectionException
     */
    public function __construct(array $childData)
    {
        foreach ($childData as $childKey => $childValues) {
            if (is_array($childValues)) {
                if (isset($childValues[XmlUtility::SPECIAL_KEYS['POSITION']])) {
                    $this->_setPosition($childValues[XmlUtility::SPECIAL_KEYS['POSITION']]);
                    unset ($childValues[XmlUtility::SPECIAL_KEYS['POSITION']]);
                }

                $onlyNodeValue = true;

                foreach ($childValues as $childValueKey => $childValue) {
                    if ($childValueKey !== XmlUtility::SPECIAL_KEYS['NODE_VALUE']) {
                        $onlyNodeValue = false;
                    }
                }

                if ($onlyNodeValue) {
                    $childData[$childKey] = $childValues[XmlUtility::SPECIAL_KEYS['NODE_VALUE']];
                }
            }
        }

        if (isset($childData[XmlUtility::SPECIAL_KEYS['ATTRIBUTES']])) {
            $this->_setAttributes($childData[XmlUtility::SPECIAL_KEYS['ATTRIBUTES']]);
        }

        if (isset($childData[XmlUtility::SPECIAL_KEYS['NODE_VALUE']])) {
            $this->_setNodeValue($childData[XmlUtility::SPECIAL_KEYS['NODE_VALUE']]);
        }

        $this->fillProperties($childData);
    }

    /**
     * @return string
     */
    public static function getTagName(): string
    {
        return XmlUtility::sanitizeTagName((new ReflectionClass(static::class))->getShortName());
    }

    /**
     * @return array
     */
    public function _getAttributes(): array
    {
        return $this->_attributes;
    }

    /**
     * @param array $attributes
     */
    public function _setAttributes(array $attributes): void
    {
        $this->_attributes = $attributes;
    }

    /**
     * @return mixed|null
     */
    public function _getNodeValue()
    {
        return $this->_nodeValue[0] ?? null;
    }

    /**
     * @param mixed $nodeValue
     */
    public function _setNodeValue($nodeValue): void
    {
        $this->_nodeValue = [$nodeValue];
    }

    /**
     * @return int|null
     */
    public function _getPosition(): ?int
    {
        return $this->_position;
    }

    /**
     * @param int $position
     */
    public function _setPosition(int $position): void
    {
        $this->_position = $position;
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function toArray(): array
    {
        $propertiesArray = ObjectUtility::toArray($this);
        $array = [];

        foreach ($propertiesArray as $key => $value) {
            $array[XmlUtility::sanitizeTagName($key)] = $value;
        }

        if (!empty($this->_getAttributes())) {
            $array[XmlUtility::SPECIAL_KEYS['ATTRIBUTES']] = $this->_getAttributes();
        }

        if (null !== $this->_getNodeValue()) {
            $array[XmlUtility::SPECIAL_KEYS['NODE_VALUE']] = $this->_getNodeValue();
        }

        if (null !== $this->_getPosition()) {
            $array[XmlUtility::SPECIAL_KEYS['POSITION']] = $this->_getPosition();
        }

        return $array;
    }
}
