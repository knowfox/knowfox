<?php

namespace Knowfox\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
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

        return view('concept.index', [
            'concepts' => $concepts->paginate(),
            'page_title' => $page_title,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  \Knowfox\Models\Concept  $concept
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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Knowfox\Models\Concept  $concept
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Concept $concept)
    {
        $this->validate($request, [
            'title' => [
                'required',
                Rule::unique('concepts')->ignore($concept->id),
                'max:255',
            ]
        ]);

        $concept->fill($request->all());
        $concept->save();

        // @todo
        $concept->fixTree();

        return redirect()->route('concept.show', [$concept])
            ->with('status', 'Concept updated (and tree fixed)');
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
