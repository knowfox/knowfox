<?php

namespace Knowfox\Observers;

use Knowfox\Models\Item;

class ItemObserver
{
    /**
     * Parse items from the concept body
     * @param Concept $concept
     */
    public function saving(Item $item)
    {
        if (!$item->done_at && $item->is_done) {
            $item->done_at = strftime('%Y-%m-%d %H:%M:%S');
        }
    }
}