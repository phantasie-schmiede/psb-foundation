<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

return [
    \PSB\PsbFoundation\Domain\Model\Typo3\FrontendUser::class => [
        'tableName' => 'fe_users',
    ],
    \PSB\PsbFoundation\Domain\Model\Typo3\Page::class         => [
        'properties' => [
            'cType' => [
                'fieldName' => 'CType',
            ],
        ],
        'tableName'  => 'pages',
    ],
    \PSB\PsbFoundation\Domain\Model\Typo3\TtContent::class    => [
        'properties' => [
            'cType' => [
                'fieldName' => 'CType',
            ],
        ],
        'tableName'  => 'tt_content',
    ],
];
