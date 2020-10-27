<?php

namespace Knowfox\Http\Controllers;

use Knowfox\Models\Concept;

class CanvasController extends Controller
{
    public function canvas(Concept $concept)
    {
        return view('knowfox::concept.canvas', [
            'concept' => $concept
        ]);
    }
}