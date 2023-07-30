<?php

namespace Knowfox\ViewComposers;

use Illuminate\View\View;
use Knowfox\Models\Concept;

class ImpactMapComposer
{
    private $map = [];

    private function traverse($parent, $tree, $path = [])
    {
        $rowspan = 1;

        foreach ($tree as $concept) {

            if ($parent->type == 'folder') {
                $concept->path = array_merge($path, [$concept->title]);
            }
            else {
                $concept->path = [$concept->title];
            }

            if ($concept->type != 'folder') {
                $this->map[] = $concept;
            }
            $rowspan += $concept->rowspan = $this->traverse($concept, $concept->children, $concept->path);
        }

        return $rowspan;
    }

    public function compose(View $view)
    {
        $this->traverse($view->concept, $view->concept->descendants->toTree());
        $view->map = $this->map;
    }
}