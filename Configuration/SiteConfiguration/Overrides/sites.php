<?php
$ll = 'LLL:EXT:psb_foundation/Resources/Private/Language/Backend/Configuration/SiteConfiguration/Overrides/sites.xlf:';

$GLOBALS['SiteConfiguration']['site']['columns']['websiteColor'] = [
    'config'      => [
        'renderType' => 'colorpicker',
        'size'       => 10,
        'type'       => 'input',
    ],
    'description' => $ll . 'websiteColor.description',
    'label'       => $ll . 'websiteColor.label',
];

$GLOBALS['SiteConfiguration']['site']['columns']['websiteLogo'] = [
    'config'      => [
        'eval'        => 'trim',
        'mode'        => 'useOrOverridePlaceholder',
        'placeholder' => 'fileadmin/user_upload/logo.svg',
        'type'        => 'input',
    ],
    'description' => $ll . 'websiteLogo.description',
    'label'       => $ll . 'websiteLogo.label',
];

$GLOBALS['SiteConfiguration']['site']['columns']['websiteSubtitle'] = [
    'config'      => [
        'eval' => 'trim',
        'type' => 'input',
    ],
    'description' => $ll . 'websiteSubtitle.description',
    'label'       => $ll . 'websiteSubtitle.label',
];

$GLOBALS['SiteConfiguration']['site']['palettes']['default']['showitem'] .= ', websiteSubtitle, --linebreak--, websiteLogo, websiteColor';
