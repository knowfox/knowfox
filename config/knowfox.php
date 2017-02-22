<?php

return [
    'relationships' => [
        'commission' => [ 'beauftragt', 'beauftragt von' ],
        'written' => [ 'hat geschrieben', 'wurde geschrieben von' ],
        'invented' => [ 'hat erfunden', 'wurde erfunden von' ],
        'similar' => [ 'ähnlich mit', 'ähnlich mit' ],
        'supports' => [ 'unterstützt', 'unterstützt von' ],
        'teacher' => [ 'Lehrer von', 'Schüler von' ],
        'translates' => [ 'übersetzt', 'übersetzt' ],
        'uses' => ['nutzt', 'wird genutzt von'],
    ],

    'languages' => [
        'de',
        'en',
    ]
];
