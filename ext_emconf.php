<?php
declare(strict_types=1);

$EM_CONF['psb_foundation'] = [
    'author'           => 'Daniel Ablass',
    'author_email'     => 'dn@phantasie-schmiede.de',
    'category'         => 'misc',
    'clearCacheOnLoad' => false,
    'constraints'      => [
        'conflicts' => [
        ],
        'depends'   => [
            'fluid_styled_content' => '10.1.0-10.4.99',
            'php'                  => '7.4',
            'typo3'                => '10.1.0-10.4.99',
        ],
        'suggests'  => [
        ],
    ],
    'createDirs'       => '',
    'description'      => 'Basic configuration for TYPO3',
    'state'            => 'beta',
    'title'            => 'PSbits | Foundation',
    'uploadfolder'     => false,
    'version'          => '0.0.0',
];
