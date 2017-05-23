<?php
/**
 * Knowfox - Personal Knowledge Management
 * Copyright (C) 2017  Olav Schettler
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
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
            'timeout'  => 10.0,
        ]);
        $response = $client->get('parser', [
            'query' => ['url' => $concept->source_url],
            'headers' => [
                'Content-Type' => 'application/json',
                'x-api-key' => config('knowfox.mercury_key'),
            ]
        ]);

        if ($response->getStatusCode() == 200 && ($parsed = json_decode($response->getBody()))) {

            if (!empty($parsed->title)) {
                $concept->title = $parsed->title;
            }
            if (!empty($parsed->excerpt)) {
                $concept->summary = $parsed->excerpt;
            }

            if (!empty($parsed->lead_image_url)) {
                $concept->body = '![Lead image](' . $parsed->lead_image_url . ")\n";
            }

            if (!empty($parsed->content)) {
                $concept->body .= $parsed->content;
            }
        }

        $concept->save();

        return view('bookmark.show', ['concept' => $concept]);
    }
}



