<?php

namespace Knowfox\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Knowfox\Models\Concept;

class JournalController extends Controller
{
    public function today()
    {
        $root = Concept::whereIsRoot()->where('title', 'Journal')->first();
        if (!$root) {
            return back()->withErrors('No "Journal" root');
        }

        $year = Concept::firstOrCreate([
            'parent_id' => $root->id,
            'title' => date('Y'),
            'owner_id' => Auth::id(),
        ]);

        $month = Concept::firstOrCreate([
            'parent_id' => $year->id,
            'title' => date('m'),
            'owner_id' => Auth::id(),
        ]);

        $concept = Concept::where('parent_id', $month->id)
            ->where('title', 'like', date('Y-m-d') . '%')
            ->first();
        if (!$concept) {
            $concept = Concept::create([
                'parent_id' => $month->id,
                'title' => date('Y-m-d l:') . ' Journal',
                'owner_id' => Auth::id(),
            ]);
            $concept->tag('Journal');
        }

        return redirect()->route('concept.show', [$concept]);
    }

}
