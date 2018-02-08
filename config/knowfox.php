<?php

return [
    'relationships' => [],

    'languages' => [
        'de',
        'en',
    ],

    'types' => [],

    'mercury_key' => env('MERCURY_KEY', 'secret key'),
    'presentation_base_path' => env('PRESENTATION_BASE', base_path()),
];
