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

use GuzzleHttp\Exception\ServerException;
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
        return response()
          ->view('knowfox::bookmark.create', ['concept' => $concept])
          ->header('Access-Control-Allow-Origin', '*');
    }

    private function parseContent($concept)
    {
        // https://mercury.postlight.com/web-parser/
        $client = new Client([
            'base_uri' => 'https://mercury.postlight.com/',
            'timeout'  => 10.0,
        ]);

        $status = null;
        $message = '';
        try {
            $response = $client->get('parser', [
                'query' => ['url' => $concept->source_url],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-api-key' => config('knowfox.mercury_key'),
                ]
            ]);
            $status = $response->getStatusCode();
        }
        catch (ServerException $e) {
            $message = 'Server error: ' . $e->getMessage();
        }

        if ($status == 200 && ($parsed = json_decode($response->getBody()))) {

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

            $message = 'Parsed URL';
        }

        if (empty($concept->title)) {
            $url = parse_url($request->input('source_url'));
            $concept->title = $url['host'] . $url['path'];
        }

        $concept->save();

        return $message;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $parent = Concept::whereIsRoot()->where('title', 'Bookmarks')->first();

        $owner_id = $request->user()->id;

        $source_url = $request->input('source_url');
        $source_urls = [$source_url];

        if (preg_match('#^https://#', $source_url)) {
            $source_urls[] = preg_replace('#^https://#', 'http://', $source_url);
        }

        if (preg_match('#^http(s?)://www\.(.*)$#', $source_url, $matches)) {
            $source_urls[] = 'http://' . $matches[2];
            $source_urls[] = 'https://' . $matches[2];
        }

        $concept = Concept::where('owner_id', $owner_id)
            ->whereIn('source_url', $source_urls)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($concept === null) {
            $concept = new Concept([
                'title' => $request->input('title'),
                'source_url' => $source_url,
            ]);
            $concept->owner_id = $owner_id;

            if ($parent) {
                $concept->appendToNode($parent);
            }

            $message = $this->parseContent($concept);
        }
        else {
            $message = 'Already stored on ' . strftime('%Y-%m-%d', strtotime($concept->updated_at));
        }

        return view('knowfox::bookmark.show', ['concept' => $concept, 'message' => $message]);
    }
}
