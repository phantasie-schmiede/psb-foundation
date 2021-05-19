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
            'php'   => '7.4',
            'typo3' => '10.4.10-11.2.99',
        ],
        'suggests'  => [
        ],
    ],
    'description'      => 'Configuration framework for TYPO3 extension development',
    'state'            => 'stable',
    'title'            => 'PSbits | Foundation',
    'version'          => '1.0.0',
];
