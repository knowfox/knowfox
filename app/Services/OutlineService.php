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

    private function traverse(Concept $concept, $outline_view)
    {
        $concept->load('descendants');

        $traverse = function ($tree) use (&$traverse, $outline_view) {

            $concepts = [];
            foreach ($tree as $concept) {
                $concepts[] = view($outline_view, [
                    'concept' => $concept,
                    'descendants' => $traverse($concept->children),
                ]);
            }
            return join("\n", $concepts);
        };

        return $traverse($concept->descendants->toTree());
    }
}
