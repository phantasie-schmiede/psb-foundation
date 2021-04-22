<?php
declare(strict_types=1);

$EM_CONF['psb_foundation'] = [
    'author'           => 'Daniel Ablass',
    'author_email'     => 'dn@phantasie-schmiede.de',
    'category'         => 'misc',
    'clearCacheOnLoad' => true,
    'constraints'      => [
        'conflicts' => [
        ],
        'depends'   => [
            'fluid_styled_content' => '10.4.10-10.4.99',
            'php'                  => '7.4',
            'typo3'                => '10.4.10-10.4.99',
        ],
        'suggests'  => [
        ],
    ],
    'createDirs'       => '',
    'description'      => 'Configuration framework for TYPO3 extension development',
    'state'            => 'stable',
    'title'            => 'PSbits | Foundation',
    'uploadfolder'     => false,
    'version'          => '1.0.0',
];
