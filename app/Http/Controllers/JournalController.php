<?php

namespace Knowfox\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Knowfox\Models\Concept;

use Carbon\Carbon;

class JournalController extends Controller
{
    public function date($date_string = null)
    {
        /** @var \Carbon\Carbon $date */
        if ($date_string) {
            $date = Carbon::createFromFormat('Y-m-d', $date_string);
            if (!$date) {
                throw \Exception("{$date_string} is not a date");
            }
        }
        else {
            $date = Carbon::today();
        }

        $root = Concept::whereIsRoot()->where('title', 'Journal')->first();
        if (!$root) {
            return back()->withErrors('No "Journal" root');
        }

        $year = Concept::firstOrCreate([
            'parent_id' => $root->id,
            'title' => $date->format('Y'),
            'owner_id' => Auth::id(),
        ]);

        $month = Concept::firstOrCreate([
            'parent_id' => $year->id,
            'title' => $date->format('m'),
            'owner_id' => Auth::id(),
        ]);

        $concept = Concept::where('parent_id', $month->id)
            ->where('title', 'like', $date->format('Y-m-d') . '%')
            ->first();
        if (!$concept) {
            $nav = '';

            if ($date->dayOfWeek == Carbon::MONDAY) {
                $friday = (clone $date)->subDay(3)->format('Y-m-d');
                $nav .= "[friday ({$friday})](/{$friday}) | ";
            }

            $prev = (clone $date)->subDay(1)->format('Y-m-d');
            $next = (clone $date)->addDay(1)->format('Y-m-d');
            $nav .= "[yesterday ({$prev})](/{$prev}) | [tomorrow ({$next})](/{$next})";

            if ($date->dayOfWeek == Carbon::FRIDAY) {
                $monday = (clone $date)->addDay(3)->format('Y-m-d');
                $nav .= "| [monday ({$monday})](/{$monday})";
            }

            $concept = Concept::create([
                'parent_id' => $month->id,
                'title' => $date->format('Y-m-d l:') . ' Journal',
                'owner_id' => Auth::id(),
                'body' => $nav . "\n\n",
            ]);
            $concept->tag('Journal');
        }

        return redirect()->route('concept.show', [$concept]);
    }
}
