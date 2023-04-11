<?php
/** @noinspection UnsupportedStringOffsetOperationsInspection */
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Service\Configuration;

use JsonException;
use PSB\PsbFoundation\Attribute\PageType;
use PSB\PsbFoundation\Attribute\PluginAction;
use PSB\PsbFoundation\Data\ExtensionInformationInterface;
use PSB\PsbFoundation\Data\PluginConfiguration;
use PSB\PsbFoundation\Service\LocalizationService;
use PSB\PsbFoundation\Utility\ReflectionUtility;
use PSB\PsbFoundation\Utility\TypoScript\PageObjectConfiguration;
use PSB\PsbFoundation\Utility\TypoScript\TypoScriptUtility;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\DataHandling\PageDoktypeRegistry;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use function in_array;
use function is_int;

/**
 * Class PluginService
 *
 * @package PSB\PsbFoundation\Service\Configuration
 */
class PluginService
{
    /**
     * This static variable is used to keep track of already registered wizard groups and is pre-filled with TYPO3's
     * default groups as defined in
     * typo3\sysext\backend\Configuration\TsConfig\Page\Mod\Wizards\NewContentElement.tsconfig
     *
     * @var string[]
     */
    private array $contentElementWizardGroups = [
        'common',
        'forms',
        'menu',
        'plugins',
        'special',
    ];

    /**
     * @param FlexFormService $flexFormService
     * @param IconRegistry $iconRegistry
     * @param LocalizationService $localizationService
     * @param PageDoktypeRegistry $pageDoktypeRegistry
     */
    public function __construct(
        protected FlexFormService     $flexFormService,
        protected IconRegistry        $iconRegistry,
        protected LocalizationService $localizationService,
        protected PageDoktypeRegistry $pageDoktypeRegistry,
    )
    {
    }

    /**
     * For use in ext_localconf.php files
     *
     * @param ExtensionInformationInterface $extensionInformation
     * @param string $group
     * @param string $pluginName
     * @param string|null $iconIdentifier
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function addPluginToElementWizard(
        ExtensionInformationInterface $extensionInformation,
        PluginConfiguration           $pluginConfiguration,
    ): void
    {
        $group = $pluginConfiguration->getGroup() ?: mb_strtolower($extensionInformation->getVendorName());
        $iconIdentifier = $pluginConfiguration->getIconIdentifier() ?: $extensionInformation->getExtensionKey() . '-' . str_replace('_', '-',
                GeneralUtility::camelCaseToLowerCaseUnderscored($pluginConfiguration->getKey()));
        $ll = 'LLL:EXT:' . $extensionInformation->getExtensionKey() . '/Resources/Private/Language/Backend/Configuration/TsConfig/Page/Mod/Wizards/newContentElement.xlf:' . $group . '.elements.' . lcfirst($pluginConfiguration->getKey());
        $description = $ll . '.description';
        $title = $ll . '.title';
        $listType = str_replace('_', '', $extensionInformation->getExtensionKey()) . '_' . mb_strtolower($pluginConfiguration->getKey());

        if (false === $this->localizationService->translationExists($description)) {
            $description = '';
        }

        if (false === $this->localizationService->translationExists($title, false)) {
            $title = $this->getDefaultLabelPathForPlugin($extensionInformation, $pluginConfiguration->getKey());

            if (false === $this->localizationService->translationExists($title)) {
                $title = $pluginConfiguration->getKey();
            }
        }

        $configuration = [
            'description' => $description,
            'iconIdentifier' => $this->iconRegistry->isRegistered($iconIdentifier) ? $iconIdentifier : 'content-plugin',
            'title' => $title,
            'tt_content_defValues' => [
                'CType' => 'list',
                'list_type' => $listType,
            ],
        ];

        $this->addElementWizardItem($configuration, $extensionInformation->getExtensionKey(), $group, $listType);
    }

    /**
     * For use in ext_localconf.php files
     *
     * @param ExtensionInformationInterface $extensionInformation
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function configurePlugins(ExtensionInformationInterface $extensionInformation): void
    {
        foreach ($extensionInformation->getPlugins() as $configuration) {
            [
                $controllersAndCachedActions,
                $controllersAndUncachedActions,
            ] = $this->collectActionsAndConfiguration($extensionInformation, $configuration);

            ExtensionUtility::configurePlugin($extensionInformation->getExtensionName(), $configuration->getKey(),
                $controllersAndCachedActions, $controllersAndUncachedActions);

            $this->addPluginToElementWizard($extensionInformation, $configuration);
        }
    }

    /**
     * For use in Configuration/TCA/Overrides/tt_content.php files
     *
     * @param ExtensionInformationInterface $extensionInformation
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     * @throws NotFoundExceptionInterface
     */
    public function registerPlugins(ExtensionInformationInterface $extensionInformation): void
    {
        foreach ($extensionInformation->getPlugins() as $configuration) {
            $title = $configuration->getTitle();

            if (empty($title)) {
                $title = $this->getDefaultLabelPathForPlugin($extensionInformation, $configuration->getKey());

                if (false === $this->localizationService->translationExists($title)) {
                    $title = $configuration->getKey();
                }
            }

            $iconIdentifier = $extensionInformation->getExtensionKey() . '-' . str_replace('_', '-',
                    GeneralUtility::camelCaseToLowerCaseUnderscored($configuration->getKey()));

            $pluginSignature = ExtensionUtility::registerPlugin($extensionInformation->getExtensionName(), $configuration->getKey(), $title,
                $this->iconRegistry->isRegistered($iconIdentifier) ? $iconIdentifier : 'content-plugin');
            $this->registerFlexFormForPlugin($extensionInformation, $configuration, $pluginSignature);
        }
    }

    /**
     * @param string $extensionKey
     * @param string $key
     *
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     */
    private function addElementWizardGroup(string $extensionKey, string $key): void
    {
        $header = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/Backend/Configuration/TsConfig/Page/Mod/Wizards/newContentElement.xlf:' . $key . '.header';
        GeneralUtility::makeInstance(LocalizationService::class)->translationExists($header);
        $pageTS['mod']['wizards']['newContentElement']['wizardItems'][$key] = [
            'header' => $header,
            'show' => '*',
        ];

        ExtensionManagementUtility::addPageTSConfig(TypoScriptUtility::convertArrayToTypoScript($pageTS));
        $this->contentElementWizardGroups[] = $key;
    }

    /**
     * @param array $configuration
     * @param string $extensionKey
     * @param string $group
     * @param string $key
     *
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws JsonException
     */
    private function addElementWizardItem(
        array  $configuration,
        string $extensionKey,
        string $group,
        string $key,
    ): void
    {
        if (!in_array($group, $this->contentElementWizardGroups, true)) {
            $this->addElementWizardGroup($extensionKey, $group);
        }

        $newPageTS['mod']['wizards']['newContentElement']['wizardItems'][$group]['elements'][$key] = $configuration;
        ExtensionManagementUtility::addPageTSConfig(TypoScriptUtility::convertArrayToTypoScript($newPageTS));
    }

    /**
     * @param ExtensionInformationInterface $extensionInformation
     * @param PluginConfiguration $configuration
     * @return array[]
     */
    private function collectActionsAndConfiguration(
        ExtensionInformationInterface $extensionInformation,
        PluginConfiguration           $configuration,
    ): array
    {
        $controllersAndCachedActions = [];
        $controllersAndUncachedActions = [];

        foreach ($configuration->getControllers() as $key => $value) {
            if (is_int($key)) {
                $controllerClassName = $value;
            } else {
                $controllerClassName = $key;
                $specifiedActions = $value;
            }

            $controller = GeneralUtility::makeInstance(ReflectionClass::class, $controllerClassName);
            $methods = $controller->getMethods();

            foreach ($methods as $method) {
                $pluginAction = ReflectionUtility::getAttributeInstance(PluginAction::class, $method);

                if (!$pluginAction instanceof PluginAction) {
                    continue;
                }

                $methodName = $method->getName();
                $actionName = mb_substr($methodName, 0, -6);

                if (isset($specifiedActions) && !in_array($actionName, $specifiedActions, true)) {
                    continue;
                }

                if (true === $pluginAction->isDefault()) {
                    array_unshift($controllersAndCachedActions[$controllerClassName], $actionName);
                } else {
                    $controllersAndCachedActions[$controllerClassName][] = $actionName;
                }

                if (true === $pluginAction->isUncached()) {
                    $controllersAndUncachedActions[$controllerClassName][] = $actionName;
                }

                $pageType = ReflectionUtility::getAttributeInstance(PageType::class, $method);

                if ($pageType instanceof PageType) {
                    $this->registerPageTypeForAction($actionName, $configuration, $controllerClassName, $extensionInformation, $pageType);
                }
            }

            unset($specifiedActions);
        }

        array_walk($controllersAndCachedActions, static function (&$value) {
            $value = implode(', ', $value);
        });

        array_walk($controllersAndUncachedActions, static function (&$value) {
            $value = implode(', ', $value);
        });

        return [
            $controllersAndCachedActions,
            $controllersAndUncachedActions,
        ];
    }

    /**
     * @param ExtensionInformationInterface $extensionInformation
     * @param string $pluginName
     *
     * @return string
     */
    private function getDefaultLabelPathForPlugin(
        ExtensionInformationInterface $extensionInformation,
        string                        $pluginName,
    ): string
    {
        return 'LLL:EXT:' . $extensionInformation->getExtensionKey() . '/Resources/Private/Language/Backend/Configuration/TCA/Overrides/tt_content.xlf:plugin.' . lcfirst($pluginName) . '.title';
    }

    /**
     * @param ExtensionInformationInterface $extensionInformation
     * @param PluginConfiguration $configuration
     * @param string $pluginSignature
     * @return void
     */
    private function registerFlexFormForPlugin(ExtensionInformationInterface $extensionInformation, PluginConfiguration $configuration, string $pluginSignature): void
    {
        if (!empty($configuration->getFlexForm())) {
            if (!str_contains($configuration->getFlexForm(), '/')) {
                $fileName = $configuration->getFlexForm();
            }
        } else {
            $fileName = $configuration->getKey();
        }

        if (isset($fileName)) {
            $flexFormFilePath = 'EXT:' . $extensionInformation->getExtensionKey() . '/Configuration/FlexForms/' . $fileName  . '.xml';
        } else {
            $flexFormFilePath = $configuration->getFlexForm();
        }

        $flexFormFilePath = GeneralUtility::getFileAbsFileName($flexFormFilePath);

        if (file_exists($flexFormFilePath)) {
            $this->flexFormService->register(file_get_contents($flexFormFilePath), $pluginSignature);
        }
    }

    /**
     * @param string $actionName
     * @param PluginConfiguration $pluginConfiguration
     * @param string $controllerClassName
     * @param ExtensionInformationInterface $extensionInformation
     * @param PageType $pageType
     * @return void
     */
    private function registerPageTypeForAction(
        string                        $actionName,
        PluginConfiguration           $pluginConfiguration,
        string                        $controllerClassName,
        ExtensionInformationInterface $extensionInformation,
        PageType                      $pageType): void
    {
        $pageObjectConfiguration = GeneralUtility::makeInstance(PageObjectConfiguration::class);
        $pageObjectConfiguration->setAction($actionName);
        $pageObjectConfiguration->setCacheable($pageType->isCacheable());
        $pageObjectConfiguration->setContentType($pageType->getContentType());
        $pageObjectConfiguration->setController($controllerClassName);
        $pageObjectConfiguration->setDisableAllHeaderCode($pageType->isDisableAllHeaderCode());
        $pageObjectConfiguration->setExtensionName($extensionInformation->getExtensionName());
        $pageObjectConfiguration->setPluginName($pluginConfiguration->getKey());
        $pageObjectConfiguration->setTypeNum($pageType->getTypeNum());
        $typoScriptObjectName = strtolower(implode('_', [
            'ajax',
            $extensionInformation['vendorName'],
            $extensionInformation['extensionName'],
            str_replace('\\', '', $pageObjectConfiguration->getController()),
            $actionName,
        ]));
        $pageObjectConfiguration->setTypoScriptObjectName($typoScriptObjectName);
        $pageObjectConfiguration->setVendorName($extensionInformation['vendorName']);
        TypoScriptUtility::registerPageType($pageObjectConfiguration);
    }
}
