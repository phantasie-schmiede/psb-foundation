<?php

$EM_CONF[$_EXTKEY] = [
    'author'           => 'Daniel Ablass',
    'author_email'     => 'dn@phantasie-schmiede.de',
    'category'         => 'misc',
    'clearCacheOnLoad' => true,
    'constraints'      => [
        'conflicts' => [],
        'depends'   => [
            'php'   => '8.1',
            'typo3' => '12.4.0-12.4.99',
        ],
        'suggests'  => [],
    ],
    'description'      => 'Configuration framework for TYPO3 extension development',
    'state'            => 'stable',
    'title'            => 'PSbits | Foundation',
    'version'          => '2.1.2',
];
