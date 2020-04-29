<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

defined('TYPO3_MODE') or die();

(static function () {
    $allExtensionInformation = \PSB\PsbFoundation\Utility\ExtensionInformationUtility::getExtensionInformation();

    foreach ($allExtensionInformation as $extensionInformation) {
        \PSB\PsbFoundation\Utility\Backend\RegistrationUtility::registerPageTypes($extensionInformation,
            \PSB\PsbFoundation\Utility\Backend\RegistrationUtility::PAGE_TYPE_REGISTRATION_MODES['TCA_OVERRIDE']);
    }

    $columnsOverrides = [
        'no_index'  => [
            'config' => [
                'default'  => true,
                'readOnly' => true,
            ],
        ],
        'no_follow' => [
            'config' => [
                'default'  => true,
                'readOnly' => true,
            ],
        ],
    ];

    // Pages of type menuDropdown shall never be indexed by search engines as they only serve as navigation placeholders.
    $GLOBALS['TCA']['pages']['types'][\PSB\PsbFoundation\Data\ExtensionInformation::PAGE_TYPE_DOKTYPES['MENU_DROPDOWN']] = array_merge($GLOBALS['TCA']['pages']['types'][\PSB\PsbFoundation\Data\ExtensionInformation::PAGE_TYPE_DOKTYPES['MENU_DROPDOWN']],
        ['columnsOverrides' => $columnsOverrides]);
})();

