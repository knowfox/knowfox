<?php

return [
    'relationships' => [],

    'languages' => [
        'de',
        'en',
    ],

    'types' => [],

    'mercury_key' => env('MERCURY_KEY', 'secret key for https://mercury.postlight.com/web-parser/'),
    'presentation_base_path' => env('PRESENTATION_BASE', base_path()),
];
