<?php

namespace Knowfox\Observers;

use Knowfox\Models\Concept;

class EbookObserver
{
    public function saving(Concept $concept)
    {
        $concept->config = (object)([
            'author' => '',
            'publisher' => '',
            'year' => '',
            'filename' => '',
            'path' => '',
            'type' => '',
            'format' => '',
        ] + (array)$concept->config);
    }
}
