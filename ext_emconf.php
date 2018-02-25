<?php
declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title'                         => 'PS | Foundation',
    'description'                   => 'basic configuration for TYPO3',
    'category'                      => 'misc',
    'author_email'                  => 'dn@phantasie-schmiede.de',
    'author'                        => 'Daniel Ablass',
    'shy'                           => '',
    'priority'                      => '',
    'module'                        => '',
    'state'                         => 'alpha',
    'internal'                      => '',
    'uploadfolder'                  => 0,
    'createDirs'                    => '',
    'modify_tables'                 => '',
    'clearCacheOnLoad'              => 0,
    'lockType'                      => '',
    'version'                       => '0.0.0',
    'constraints'                   => [
        'depends'   => [
            'extbase' => '',
            'fluid'   => '',
            'typo3'   => '8.7.0-8.7.99',
        ],
        'conflicts' => [
        ],
        'suggests'  => [
        ],
    ],
    '_md5_values_when_last_written' => '',
];
