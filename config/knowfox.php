<?php

return [
    'relationships' => [
        'commission' => [ 'beauftragt', 'beauftragt von' ],
        'founded' => [ 'hat gegründet', 'gegründet von' ],
        'invented' => [ 'hat erfunden', 'erfunden von' ],
        'platform' => [ 'stellt Plattform bereit für', 'nutzt Plattform von' ],
        'similar' => [ 'ähnlich mit', 'ähnlich mit' ],
        'supports' => [ 'unterstützt', 'unterstützt von' ],
        'teacher' => [ 'Lehrer von', 'Schüler von' ],
        'translates' => [ 'übersetzt', 'übersetzt' ],
        'uses' => ['nutzt', 'genutzt von'],
        'written' => [ 'hat geschrieben', 'geschrieben von' ],
    ],

    'languages' => [
        'de',
        'en',
    ],

    'mercury_key' => env('MERCURY_KEY', 'secret key for https://mercury.postlight.com/web-parser/'),
];
