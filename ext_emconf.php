<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'TYPO3 TipTap',
    'state' => 'stable',
    'author' => 'In2code GmbH',
    'author_email' => 'info@in2code.de',
    'version' => '0.0.17',
    'constraints' => [
        'depends' => [
            'php' => '8.3.0-8.4.99',
            'typo3' => '14.0.0-14.9.99',
            'backend' => '14.0.0-14.9.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'workspaces' => '14.0.0-14.9.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'In2code\\Typo3TipTap\\' => 'Classes',
        ],
    ],
];
