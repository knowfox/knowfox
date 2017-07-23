<?php

namespace Knowfox\Http\Controllers;

use Illuminate\Http\Request;
use Knowfox\Models\Concept;
use Knowfox\Jobs\PublishWebsite;

class WebsiteController extends Controller
{
    public function publish(Request $request, Concept $concept)
    {
        if (empty(env('WEBSITE_' . $concept->id))) {
            return redirect()->route('concept.show', [$concept])
                ->with('status', 'Not a website');
        }
        if ($concept->owner_id != $request->user()->id) {
            return redirect()->route('concept.show', [$concept])
                ->with('status', 'Not permitted');
        }

        $domain_name = trim($concept->title);

        dispatch(new PublishWebsite($request->user(), $domain_name));

        return redirect()->route('concept.show', [$concept])
            ->with('status', "Publishing of website {$domain_name} initiated");
    }
}