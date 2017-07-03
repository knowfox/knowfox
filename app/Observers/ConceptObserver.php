<?php

namespace Knowfox\Observers;

use Illuminate\Support\Facades\Auth;
use Knowfox\Models\Concept;

class ConceptObserver
{
    /**
     * Make sure the concept to be created has its owner field set,
     * eg. during outlining
     * @param \Knowfox\Models\Concept $concept
     */
    public function creating(Concept $concept)
    {
        if (empty($concept->owner_id)) {
            $concept->owner_id = Auth::id();
        }
    }

    /**
     * Update the children_count in a concept's parent
     */
    public function saved(Concept $concept)
    {
        if ($concept->isDirty(['parent_id']) && $concept->parent_id) {
            $parent = Concept::find($concept->parent_id);
            $parent->children_count = Concept::where('parent_id', $parent->id)->count();
            $parent->save();
        }
    }

    public function deleted(Concept $concept)
    {
        $parent = Concept::find($concept->parent_id);
        $parent->children_count = Concept::where('parent_id', $parent->id)->count();
        $parent->save();
    }
}