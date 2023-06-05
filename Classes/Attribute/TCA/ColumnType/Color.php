<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Attribute\TCA\ColumnType;

use Attribute;
use JsonException;
use PSB\PsbFoundation\Service\LocalizationService;
use PSB\PsbFoundation\Utility\ArrayUtility;
use PSB\PsbFoundation\Utility\Configuration\FilePathUtility;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

/**
 * Class Color
 *
 * @package PSB\PsbFoundation\Attribute\TCA\ColumnType
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Color extends AbstractColumnType implements ColumnTypeWithItemsInterface
{
    public const DATABASE_DEFINITION = 'tinyint(4) DEFAULT \'0\'';

    /**
     * @param array $items https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Check/Properties/Items.html
     * @param array $valuePicker https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Color/Properties/ValuePicker.html
     */
    public function __construct(
        protected array $items = [],
        protected array $valuePicker = [],
    ) {
    }

    /**
     * @return array
     */
    public function getValuePicker(): array
    {
        return array_merge($this->valuePicker, ['items' => $this->items]);
    }

    /**
     * @param LocalizationService $localizationService
     * @param string              $labelPath
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function processItems(LocalizationService $localizationService, string $labelPath = ''): void
    {
        // $items already has TCA format
        if (ArrayUtility::isMultiDimensionalArray($this->items)) {
            $this->processTcaFormat($localizationService);
        }

        // $items has to be transformed into TCA format
        $this->processSimpleFormat($localizationService, $labelPath);
    }

    /**
     * @param LocalizationService $localizationService
     * @param string              $labelPath
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    private function processSimpleFormat(LocalizationService $localizationService, string $labelPath = ''): void
    {
        $selectItems = [];

        foreach ($this->items as $key => $value) {
            if (!empty($labelPath) && !str_starts_with($key, FilePathUtility::LANGUAGE_LABEL_PREFIX)) {
                $identifier = GeneralUtility::underscoredToLowerCamelCase((string)$key);
                $label = rtrim($labelPath, ':') . ':' . $identifier;
            } else {
                $label = $key;
            }

            if (!str_starts_with($label, FilePathUtility::LANGUAGE_LABEL_PREFIX) || !$localizationService->translationExists($label)) {
                $label = is_string($key) ? $key : (string)$value;
            }

            $selectItems[] = [
                $label,
                $value,
            ];
        }

        $this->items = $selectItems;
    }

    /**
     * @param LocalizationService $localizationService
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    private function processTcaFormat(LocalizationService $localizationService): void
    {
        foreach ($this->items as $item) {
            $label = $item[0];

            if (str_starts_with($label, FilePathUtility::LANGUAGE_LABEL_PREFIX)) {
                $localizationService->translationExists($label);
            }
        }
    }
}