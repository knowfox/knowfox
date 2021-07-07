<?php

namespace Knowfox\ViewComposers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Knowfox\Models\Concept;

class AlphaIndexComposer
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function compose(View $view)
    {
        $letters = [];
        foreach ($view->concept->letters()->get() as $letter) {
            if ($letter->t < 'A') {
                if (empty($letters['#'])) {
                    $letters['#'] = 0;
                }
                $letters['#'] += $letter->n;
            }
            else {
                $uppercase = ucfirst($letter->t);
                if (empty($letters[$uppercase])) {
                    $letters[$uppercase] = 0;
                }
                $letters[$uppercase] += $letter->n;
            }
        }
        $view->letters = $letters;
    }
}
