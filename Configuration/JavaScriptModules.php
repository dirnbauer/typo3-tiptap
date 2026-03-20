<?php

return [
    'dependencies' => [
        'backend',
    ],
    'tags' => [
        'backend.form',
    ],
    'imports' => [
        '@typo3-tiptap/tiptap/' => [
            'path' => 'EXT:typo3_tiptap/Resources/Public/TipTap/',
        ],
    ],
];
