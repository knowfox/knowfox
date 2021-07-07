<?php

namespace Knowfox\Observers;

use Knowfox\Models\Concept;

class EbookObserver
{
    public function saving(Concept $concept)
    {
        $concept->config = (object)((array)$concept->config + [
            'author' => '',
            'publisher' => '',
            'year' => '',
            'filename' => '',
            'path' => '',
            'type' => '',
            'format' => '',
        ]);
    }
}
