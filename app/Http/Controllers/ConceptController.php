<?php

namespace Knowfox\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Knowfox\Http\Requests\ConceptRequest;
use Knowfox\Models\Concept;
use Illuminate\Http\Request;
use Knowfox\Services\PictureService;
use Validator;
use Knowfox\User;

class ConceptController extends Controller
{
    const PICTURES_DIR = 'uploads/';

    private static $validateImageRules = [
        'upload' => 'sometimes|image|mimes:jpeg,png|min:1|max:10000',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $flagged = false)
    {
        $concepts = Concept::withDepth()
            ->with('tagged')
            ->where('owner_id', Auth::id())
            ->orderBy('updated_at', "desc");

        $page_title = 'Concepts';

        if ($flagged) {
            $page_title = 'Flagged concepts';
            $concepts->where('is_flagged', 1);
        }

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

        if ($request->has('tags')) {
            $concept->tag($request->input('tags'));
        }
        else {
            $concept->untag();
        }

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
        $this->authorize('view', $concept);

        $concept->load('related', 'inverseRelated', 'tagged');

        return view('concept.show', [
            'page_title' => $concept->title,
            'concept' => $concept,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Knowfox\Models\Concept  $concept
     * @return \Illuminate\Http\Response
     */
    public function edit(Concept $concept)
    {
        $this->authorize('update', $concept);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  ConceptRequest  $request
     * @param  Concept  $concept
     * @return \Illuminate\Http\Response
     */
    public function update(PictureService $picture, ConceptRequest $request, Concept $concept)
    {
        $concept->fill($request->all());

        if (!$request->has('parent_id')) {
            $concept->makeRoot();
        }

        $concept->is_flagged = $request->has('is_flagged');

        $concept->save();

        if ($request->has('tags')) {
            $concept->retag($request->input('tags'));
        }
        else {
            $concept->untag();
        }

        $filename = '';
        if ($request->hasFile('upload')) {
            $filename = $picture->handleUpload(
                $request->file('upload'),
                self::PICTURES_DIR
            );
            $concept->image = $filename;
            $concept->save();
        }
        return redirect()->route('concept.show', [$concept])
            ->with('status', 'Concept updated ' . $filename);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Knowfox\Models\Concept  $concept
     * @return \Illuminate\Http\Response
     */
    public function destroy(Concept $concept)
    {
        $this->authorize('delete', $concept);
    }

    public function uploadImage(PictureService $picture, Request $request)
    {
        $validation = Validator::make($request->all(), self::$validateImageRules);

        if ($validation->fails()) {
            return redirect()->back()->withInput()
                ->with('errors', $validation->errors());
        }

        if (!$request->hasFile('upload')) {
            return response()->setContent('No file');
        }

        $filename = $picture->handleUpload(
            $request->file('upload'),
            self::PICTURES_DIR
        );

        return redirect()->route('concept.show', [$concept])
            ->with('status', 'Image uploaded: ' . $filename);
    }

    public function medium(PictureService $picture, $hash, $style_name, $focalpoint = NULL)
    {
        $path = self::PICTURES_DIR . $hash . '/original.jpeg';
        return $picture->image($path, $style_name, $focalpoint);
    }
}
