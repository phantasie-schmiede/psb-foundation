<?php
declare(strict_types=1);

$EM_CONF['psb_foundation'] = [
    'author'           => 'Daniel Ablass',
    'author_email'     => 'dn@phantasie-schmiede.de',
    'category'         => 'misc',
    'clearCacheOnLoad' => false,
    'constraints'      => [
        'depends'   => [
            'fluid_styled_content' => '9.5.7-9.5.99',
            'typo3'                => '9.5.7-9.5.99',
        ],
        'conflicts' => [
        ],
        'suggests'  => [
        ],
    ],
    'createDirs'       => '',
    'description'      => 'Basic configuration for TYPO3',
    'state'            => 'alpha',
    'title'            => 'PSbits | Foundation',
    'uploadfolder'     => false,
    'version'          => '0.0.0',
];
