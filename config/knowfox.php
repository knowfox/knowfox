<?php

return [
    'relationships' => [],

    'languages' => [
        'de',
        'en',
    ],

    'types' => [
        'concept',
        'ebook',
        'book list',
        'journal',
        'impact map',
        'folder',
        'feed item',
        'website',
    ],

    'styles' => [
        'h80' => [
            'height' => 80,
        ],
        'thumbnail' => [
            'width' => 150,
            //'height' => 150,
        ],
        'square' => [
            'width' => 384,
            'height' => 384,
        ],
        'text' => [
            'width' => 616,
            'height' => 410
        ],
    ],

    'mercury_key' => env('MERCURY_KEY', 'secret key'),
    'presentation_base_path' => env('PRESENTATION_BASE', base_path()),
];
