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
use Knowfox\Services\OutlineService;
use vipnytt\OPMLParser;

class OutlineController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  Concept  $concept
     * @return \Illuminate\Http\Response
     */
    public function opml(OutlineService $outline, Concept $concept)
    {
        $this->authorize('view', $concept);

        return response(
            $outline->render($concept, 'concept.opml', 'partials.outline'),
            200
        )
        ->header('Content-type', 'text/x-opml');
    }

    private function convertArray(&$ary)
    {
        foreach (array_keys($ary) as $n) {
            foreach (array_keys($ary[$n]) as $key) {
                if ($key == '@outlines') {
                    $ary[$n]['children'] = &$ary[$n]['@outlines'];
                    unset($ary[$n]['@outlines']);

                    $this->convertArray($ary[$n]['children']);
                }
                else
                if ($key == 'text') {
                    $ary[$n]['title'] = &$ary[$n]['text'];
                    unset($ary[$n]['text']);
                }
            }
        }
    }

    public function update(Request $request, Concept $concept)
    {
        $opml = $request->input('opml');
        $parser = new OPMLParser($opml);

        $data = $parser->getResult();
        $this->convertArray($data['body']);

        $count = Concept::whereDescendantOrSelf($concept->id)
            ->rebuildTree($data['body'], true);

        return response()->json(['changed' => $count]);
    }

    /**
     * Display the specified resource using Graphviz.
     *
     * @param  Concept  $concept
     * @return \Illuminate\Http\Response
     */
    public function outline(Request $request, Concept $concept)
    {
        $this->authorize('view', $concept);

        $concept->load('related', 'inverseRelated', 'tagged');

        return view('concept.outline', [
            'page_title' => $concept->title,
            'concept' => $concept,
            'is_owner' => $concept->owner_id == $request->user()->id,
            'can_update' => $request->user()->can('update', $concept),
        ]);
    }
}
