<?php

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

        $count = Concept::rebuildTree($data['body'], false);

        return response()->json(['changed' => $count]);
    }

    /**
     * Display the specified resource using Graphviz.
     *
     * @param  Concept  $concept
     * @return \Illuminate\Http\Response
     */
    public function outline(Concept $concept)
    {
        $this->authorize('view', $concept);

        $concept->load('related', 'inverseRelated', 'tagged');

        return view('concept.outline', [
            'page_title' => $concept->title,
            'concept' => $concept,
        ]);
    }
}
