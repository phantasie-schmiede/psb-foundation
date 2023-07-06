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
use PSB\PsbFoundation\Enum\CheckboxRenderType;
use PSB\PsbFoundation\Exceptions\MisconfiguredTcaException;
use PSB\PsbFoundation\Service\LocalizationService;
use PSB\PsbFoundation\Utility\ArrayUtility;
use PSB\PsbFoundation\Utility\Configuration\FilePathUtility;
use PSB\PsbFoundation\Utility\Database\DefinitionUtility;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

/**
 * Class Check
 *
 * @package PSB\PsbFoundation\Attribute\TCA\ColumnType
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Check extends AbstractColumnType implements ColumnTypeWithItemsInterface
{
    /**
     * The parameters $maximumRecordsChecked and $maximumRecordsCheckedInPid are used for the TCA properties eval and
     * validation.
     *
     * @param int|string         $cols               https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Check/Properties/Cols.html
     * @param string             $eval               https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Check/Properties/Eval.html
     * @param false|boolean      $invertStateDisplay https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Check/Properties/InvertStateDisplay.html
     * @param array              $items              https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Check/Properties/Items.html
     * @param int                $maximumRecordsChecked
     * @param int                $maximumRecordsCheckedInPid
     * @param CheckboxRenderType $renderType         https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Check/Properties/RenderType.html
     * @param array|null         $validation         https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Check/Properties/Validation.html
     *
     * @throws MisconfiguredTcaException
     */
    public function __construct(
        protected int|string         $cols = 1,
        protected string             $eval = '',
        protected bool               $invertStateDisplay = false,
        protected array              $items = [],
        protected int                $maximumRecordsChecked = 0,
        protected int                $maximumRecordsCheckedInPid = 0,
        protected CheckboxRenderType $renderType = CheckboxRenderType::default,
        protected array|null         $validation = null,
    ) {
        if (!is_int($cols) && 'inline' !== $cols) {
            throw new MisconfiguredTcaException(__CLASS__ . ': Invalid value for "cols"! (' . $cols . ')', 1681830487);
        }
    }

    /**
     * @return int|string
     */
    public function getCols(): int|string
    {
        return $this->cols;
    }

    /**
     * @return string
     */
    public function getDatabaseDefinition(): string
    {
        return DefinitionUtility::tinyint(unsigned: true);
    }

    /**
     * @return string|null
     */
    public function getEval(): ?string
    {
        $validation = null;

        if (!empty($this->validation)) {
            $validation = [$this->validation];
        }

        if (0 < $this->maximumRecordsChecked) {
            $validation[] = 'maximumRecordsChecked';
        }

        if (0 < $this->maximumRecordsCheckedInPid) {
            $validation[] = 'maximumRecordsCheckedInPid';
        }

        return $validation ? implode(', ', $validation) : null;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return string|null
     */
    public function getRenderType(): ?string
    {
        if (CheckboxRenderType::default === $this->renderType) {
            return null;
        }

        return $this->renderType->value;
    }

    /**
     * @return array|null
     */
    public function getValidation(): ?array
    {
        $validation = null;

        if (0 < $this->maximumRecordsChecked) {
            $validation['maximumRecordsChecked'] = $this->maximumRecordsChecked;
        }

        if (0 < $this->maximumRecordsCheckedInPid) {
            $validation['maximumRecordsCheckedInPid'] = $this->maximumRecordsCheckedInPid;
        }

        return $validation;
    }

    /**
     * @return bool
     */
    public function isInvertStateDisplay(): bool
    {
        return $this->invertStateDisplay;
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
        if (!is_array($this->items)) {
            return;
        }

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
            if (!is_string($key)
                && (is_string($value)
                    || is_numeric($value)
                )
            ) {
                $label = (string)$value;
            } else {
                $label = (string)$key;
            }

            if (!empty($labelPath) && !str_starts_with($label, FilePathUtility::LANGUAGE_LABEL_PREFIX)) {
                $label = $labelPath . GeneralUtility::underscoredToLowerCamelCase($label);
            }

            if (str_starts_with($label, FilePathUtility::LANGUAGE_LABEL_PREFIX)) {
                $localizationService->translationExists($label);
            }

            $selectItems[] = [
                'label' => $label,
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
            foreach ([
                 'label',
                 'labelChecked',
                 'labelUnchecked',
             ] as $key) {
                if (!empty($item[$key]) && str_starts_with($item[$key], FilePathUtility::LANGUAGE_LABEL_PREFIX)) {
                    $localizationService->translationExists($item[$key]);
                }
            }
        }
    }
}
