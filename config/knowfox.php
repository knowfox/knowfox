<?php

return [
    'relationships' => [
        'commission' => [ 'beauftragt', 'beauftragt von' ],
        'invented' => [ 'hat erfunden', 'wurde erfunden von' ],
        'platform' => [ 'stellt Plattform bereit für', 'nutzt Plattform von' ],
        'similar' => [ 'ähnlich mit', 'ähnlich mit' ],
        'supports' => [ 'unterstützt', 'unterstützt von' ],
        'teacher' => [ 'Lehrer von', 'Schüler von' ],
        'translates' => [ 'übersetzt', 'übersetzt' ],
        'uses' => ['nutzt', 'wird genutzt von'],
        'written' => [ 'hat geschrieben', 'wurde geschrieben von' ],
    ],

    'languages' => [
        'de',
        'en',
    ]
];
