<?php

return [
    'relationships' => [
        'similar' => [ 'similar to', 'similar to' ]
    ],

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

    'mercury_key' => env('MERCURY_KEY', 'secret key'),
    'presentation_base_path' => env('PRESENTATION_BASE', base_path()),
];
