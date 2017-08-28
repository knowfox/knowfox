<?php

namespace Knowfox\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Knowfox\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request, $page_title = 'Tasks', $items = null)
    {
        $show_done = false;
        if (!$items) {
            $items = Item::orderBy('is_done', 'asc')
                ->orderBy('due_at', 'asc');
            $show_done = true;
        }

        if ($request->has('tag')) {
            $items->withAllTags([$request->input('tag')]);
            $page_title .= ' with tag "' . $request->input('tag') . '"';
        }
        $items->with('tagged', 'persons')
            ->where('owner_id', Auth::id());

        return view('item.index', [
            'page_title' => $page_title,
            'items' => $items->paginate(),
            'show_done' => $show_done,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function todo(Request $request)
    {
        $items = Item::where('is_done', false)
            ->orderBy('due_at', 'asc');

        return $this->index($request,'Tasks', $items);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function done(Request $request)
    {
        $items = Item::where('is_done', true)
            ->orderBy('due_at', 'desc');

        return $this->index($request,'Completed tasks', $items);
    }
}
