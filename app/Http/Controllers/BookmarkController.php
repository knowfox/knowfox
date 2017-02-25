<?php

namespace Knowfox\Http\Controllers;

use Illuminate\Http\Request;
use Knowfox\Models\Concept;
use GuzzleHttp\Client;

class BookmarkController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $concept = new Concept([
            'title' => $request->input('title'),
            'source_url' => $request->input('url'),
        ]);
        return view('bookmark.create', ['concept' => $concept]);
//            ->header('Access-Control-Allow-Origin', '*');

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $concept = new Concept([
            'title' => $request->input('title'),
            'source_url' => $request->input('source_url'),
        ]);
        $concept->owner_id = $request->user()->id;

        $parent = Concept::whereIsRoot()->where('title', 'Bookmarks')->first();
        if ($parent) {
            $concept->appendToNode($parent);
        }

        // https://mercury.postlight.com/web-parser/
        $client = new Client([
            'base_uri' => 'https://mercury.postlight.com/',
            'timeout'  => 2.0,
        ]);
        $response = $client->get('parser', [
            'query' => ['url' => $concept->source_url],
            'headers' => [
                'Content-Type' => 'application/json',
                'x-api-key' => config('knowfox.mercury_key'),
            ]
        ]);

        if ($response->getStatusCode() == 200 && ($parsed = json_decode($response->getBody()))) {

            $concept->title = $parsed->title;
            $concept->summary = $parsed->excerpt;

            if ($parsed->lead_image_url) {
                $concept->body = '![Lead image](' . $parsed->lead_image_url . ")\n";
            }

            $concept->body .= $parsed->content;
        }

        $concept->save();

        return view('bookmark.show', ['concept' => $concept]);
    }
}



