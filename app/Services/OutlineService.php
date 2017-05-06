<?php

namespace Knowfox\Services;

use Knowfox\Models\Concept;

class OutlineService
{
    public function render(Concept $concept, $container_view, $outline_view)
    {
        return view($container_view, [
            'concept' => $concept,
            'tree' => view($outline_view, [
                'concept' => $concept,
                'descendants' => $this->traverse($concept, $outline_view),
            ]),
        ]);
    }

    public function renderDescendents(Concept $concept, $container_view, $outline_view)
    {
        return view($container_view, [
            'concept' => $concept,
            'descendants' => $this->traverse($concept, $outline_view),
        ]);
    }

    public function traverse(Concept $concept, $outline_view, $callback = null, $preprocess = null, $by_date = false)
    {
        $concept->load('descendants');

        $traverse = function ($tree) use (&$traverse, $outline_view, $callback, $preprocess) {

            $concepts = [];
            foreach ($tree as $concept) {

                if ($preprocess) {
                    call_user_func($preprocess, $concept);
                }

                $concepts[] = (object)[
                    'concept' => $concept,
                    'rendered' => view($outline_view, [
                        'concept' => $concept,
                        'descendants' => call_user_func($traverse, $concept->children),
                    ]),
                ];
            }

            if ($callback) {
                return call_user_func($callback, $concepts);
            }
            else {
                return join("\n", array_map(
                    function ($item) { return $item->rendered; },
                    $concepts
                ));
            }
        };

        if ($by_date) {
            return call_user_func($traverse,
                $concept->descendants()->orderBy('created_at', 'desc')->get()->toTree()
            );
        }
        return call_user_func($traverse, $concept->descendants->toTree());
    }
}
