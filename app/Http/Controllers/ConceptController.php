<?php

namespace Knowfox\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Knowfox\Http\Requests\ConceptRequest;
use Knowfox\Models\Concept;
use Illuminate\Http\Request;

class ConceptController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $concepts = Concept::withDepth()
            ->with('tagged')
            ->where('owner_id', Auth::id())
            ->orderBy('updated_at');

        $page_title = 'Concepts';

        if ($request->has('tag')) {
            $concepts->withAllTags([$request->input('tag')]);
            $page_title .= ' with tag "' . $request->input('tag') . '"';
        }

        if ($request->has('q')) {
            $concepts->where('title', 'like', '%' . $request->input('q') . '%');
        }

        if ($request->has('except')) {
            $concepts->where('id', '!=', $request->input('except'));
        }

        if ($request->format() == 'json') {
            $items = $concepts
                ->select('id', 'title')
                ->paginate();
            return response()->json($items);
        }
        else {
            return view('concept.index', [
                'concepts' => $concepts->paginate(),
                'page_title' => $page_title,
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('concept.create', ['concept' => new Concept()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  ConceptRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ConceptRequest $request)
    {
        $concept = new Concept($request->all());
        $concept->owner_id = $request->user()->id;
        $concept->save();

        return redirect()->route('concept.show', [$concept])
            ->with('status', 'Concept created');
    }

    /**
     * Display the specified resource.
     *
     * @param  Concept  $concept
     * @return \Illuminate\Http\Response
     */
    public function show(Concept $concept)
    {
        $concept->load('related', 'inverseRelated', 'tagged');

        return view('concept.show', ['concept' => $concept]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Knowfox\Models\Concept  $concept
     * @return \Illuminate\Http\Response
     */
    public function edit(Concept $concept)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  ConceptRequest  $request
     * @param  Concept  $concept
     * @return \Illuminate\Http\Response
     */
    public function update(ConceptRequest $request, Concept $concept)
    {
        $concept->fill($request->all());

        if (!$request->has('parent_id')) {
            $concept->makeRoot();
        }

        $concept->save();

        // @todo
        //$concept->fixTree();

        return redirect()->route('concept.show', [$concept])
            ->with('status', 'Concept updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Knowfox\Models\Concept  $concept
     * @return \Illuminate\Http\Response
     */
    public function destroy(Concept $concept)
    {
        //
    }
}
