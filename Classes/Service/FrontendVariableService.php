<?php
declare(strict_types=1);

/*
 * This file is part of PSB Foundation.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace PSB\PsbFoundation\Service;

use PSB\PsbFoundation\Data\ExtensionInformation;
use PSB\PsbFoundation\Domain\Model\FrontendVariable;
use PSB\PsbFoundation\Domain\Repository\FrontendVariableRepository;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;

/**
 * Class FrontendVariableService
 *
 * @package PSB\PsbFoundation\Service
 */
class FrontendVariableService
{
    public function __construct(
        protected readonly ExtensionInformation        $extensionInformation,
        protected readonly ExtensionInformationService $extensionInformationService,
        protected readonly FrontendVariableRepository  $frontendVariableRepository,
    ) {
    }

    /**
     * @return mixed
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function getMarkerBegin(): string
    {
        return $this->getSettings()['markerBegin'];
    }

    /**
     * @return mixed
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function getMarkerEnd(): string
    {
        return $this->getSettings()['markerEnd'];
    }

    /**
     * @param array<string, FrontendVariable> $variables
     *
     * @return array
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function getReplacementArray(array $variables): array
    {
        $replacementArray = [];

        foreach ($variables as $variable) {
            $maskedName = $this->getMarkerBegin() . $variable->getName() . $this->getMarkerEnd();
            $replacementArray[$maskedName] = $variable->getValue();
        }

        return $replacementArray;
    }

    /**
     * @param int $pid
     *
     * @return FrontendVariable[];
     */
    public function getVariablesForRootline(int $pid): array
    {
        $frontendVariables = [];
        $variables = $this->frontendVariableRepository->findByPid(0);

        /** @var FrontendVariable $variable */
        foreach ($variables as $variable) {
            $frontendVariables[$variable->getName()] = $variable;
        }

        $rootLine = array_reverse(
            GeneralUtility::makeInstance(RootlineUtility::class, $pid)
                ->get()
        );

        foreach ($rootLine as $page) {
            $variables = $this->frontendVariableRepository->findByPid($page['uid']);

            /** @var FrontendVariable $variable */
            foreach ($variables as $variable) {
                $frontendVariables[$variable->getName()] = $variable;
            }
        }

        return $frontendVariables;
    }

    /**
     * @return bool
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function isEnabled(): bool
    {
        return (bool)$this->getSettings()['enabled'];
    }

    /**
     * @return array
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    private function getSettings(): array
    {
        return $this->extensionInformationService->getConfiguration(
            $this->extensionInformation,
            'features.frontendVariables'
        );
    }
}
