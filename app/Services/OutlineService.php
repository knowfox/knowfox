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

    public function traverse(Concept $concept, $outline_view, $callback = null)
    {
        $concept->load('descendants');

        $traverse = function ($tree) use (&$traverse, $outline_view, $callback) {

            $concepts = [];
            foreach ($tree as $concept) {
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

        return call_user_func($traverse, $concept->descendants->toTree());
    }
}
