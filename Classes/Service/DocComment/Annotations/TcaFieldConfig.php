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

use PSB\PsbFoundation\Utility\ExtensionInformationUtility;
use PSB\PsbFoundation\Utility\StringUtility;
use ReflectionException;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;

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
     * @var string
     */
    protected string $foreignTable = '';

    /**
     * @var array
     */
    protected array $items = [];

    /**
     * @var string
     */
    protected string $linkedModel = '';

    /**
     * @var string
     */
    protected string $type = '';

    /**
     * TcaFieldConfig constructor.
     *
     * @param array $data
     *
     * @throws NoSuchCacheException
     * @throws ReflectionException
     */
    public function __construct(array $data)
    {
        if ('select' === $data['type']) {
            // instead of directly specifying a foreign table, it is possible to specify a domain model class instead
            if (isset ($data['linkedModel'])) {
                $data['foreignTable'] = ExtensionInformationUtility::convertClassNameToTableName($data['linkedModel']);
            }

            //            if (isset ($data['items']) && is_array($data['items'])) {
            //                // transform associative array to simple array for TCA
            //                $data['items'] = array_map(static function ($key, $value) {
            //                    return [ucwords(str_replace('_', ' ', mb_strtolower($key))), $value];
            //                }, array_keys($data['items']), array_values($data['items']));
            //            }
        }

        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function getForeignTable(): string
    {
        return $this->foreignTable;
    }

    /**
     * @param string $foreignTable
     */
    public function setForeignTable(string $foreignTable): void
    {
        $this->foreignTable = $foreignTable;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param array $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * @return string
     */
    public function getLinkedModel(): string
    {
        return $this->linkedModel;
    }

    /**
     * @param string $linkedModel
     */
    public function setLinkedModel(string $linkedModel): void
    {
        $this->linkedModel = $linkedModel;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
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
