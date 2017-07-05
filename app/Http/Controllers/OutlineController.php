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
use Illuminate\Support\Facades\DB;
use Knowfox\Models\Concept;
use Knowfox\Services\OutlineService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    public function update(OutlineService $outline, Request $request, Concept $concept)
    {
        $opml = $request->input('opml');
        $parser = new OPMLParser($opml);
        $data = $parser->getResult();

        try {
            DB::transaction(function () use (&$count, &$fails, $outline, $concept, $data) {
                list ($count, $fails) = $outline->update($concept, $data);
                if ($fails) {
                    throw \Exception("Not saved. " . count($fails) . ' fails.');
                }
            });
        }
        catch(\Exception $e) {}

        return response()->json(['changed' => $count, 'fails' => array_map(function ($c) {
            return ['id' => $c->id, 'title' => $c->title]; }, $fails)]);
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

    public function json(Request $request)
    {
        if (!$request->node) {
            throw new NotFoundHttpException('Needs an ID');
        }
        $concept = Concept::find($request->node);
        if (!$concept) {
            throw new NotFoundHttpException('Concept ' . (int)$request->node . ' not found');
        }
        $this->authorize('view', $concept);

        $children = $concept->children()->defaultOrder()->get()->map(function ($child) {
            return [
                'id' => $child->id,
                'label' => $child->title,
                'load_on_demand' => $child->children->count() > 0,
                'summary' => $child->summary,
                'body' => $child->renderedBody,
            ];
        });

        return response()->json($children);
    }

    public function updateJson(Request $request)
    {
        $op = $request->op;

        switch ($op) {
            case 'move':
                $concept = Concept::find($request->id);
                $parent = Concept::find($request->parent);
                if (! $request->next) {
                    $parent->appendNode($concept);
                }
                else {
                    $concept->insertBeforeNode(Concept::find($request->next));
                }
                break;

            default:
                throw new \Exception("Operation {$op} not supported");
        }
        return response()->json([
            'status' => 'ok',
        ]);
    }
}
