<?php

namespace Knowfox\Http\Controllers;

use Conner\Tagging\Model\Tag;
use Illuminate\Http\Request;

class TagsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $tags = Tag::select('slug', 'name');

        if ($request->has('q')) {
            $tags->where('name', 'like', '%' . $request->input('q') . '%');
        }

        return response()->json($tags->paginate());
    }
}
