<?php
/**
 * Knowfox - Personal Knowledge Management
 * Copyright (C) 2017  Olav Schettler
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace Knowfox\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Knowfox\Http\Requests\ConceptRequest;
use Knowfox\Jobs\PublishPresentation;
use Knowfox\Models\Concept;
use Illuminate\Http\Request;
use Knowfox\Services\PictureService;
use Validator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\File;
use Illuminate\Support\Facades\View;

class ConceptController extends Controller
{
    private static $validateImageRules = [
        'upload' => 'sometimes|image|mimes:jpeg,png|min:1|max:10000',
    ];

    public function toplevel(Request $request)
    {
        return $this->index($request, 'toplevel');
    }

    public function flagged(Request $request)
    {
        return $this->index($request, 'flagged');
    }

    public function popular(Request $request)
    {
        return $this->index($request, 'popular');
    }

    public function shares(Request $request)
    {
        return $this->index($request, 'shares');
    }

    public function shared(Request $request)
    {
        return $this->index($request, 'shared');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $special = false)
    {
        $concepts = Concept::withDepth()
            ->with('tagged');

        $page_title = 'Concepts';

        if ($special) {
            switch ($special) {
                case 'flagged':
                    $page_title = 'Flagged concepts';
                    $concepts->where('is_flagged', 1);
                    break;
                case 'toplevel':
                    $page_title = 'Toplevel concepts';
                    $concepts->whereIsRoot();
                    break;
                case 'popular':
                    $page_title = 'Popular concepts';
                    $concepts->orderBy('viewed_count', 'desc');
                    break;
                case 'shares':
                    $page_title = 'Concepts shared by me';
                    $concepts->has('shares');
                    break;
                case 'shared':
                    $page_title = 'Concepts shared with me';
                    $concepts->whereHas('shares', function ($query) {
                        $query->where('users.id', Auth::id());
                    });
                    break;
            }
        }

        $concepts
            ->orderBy('viewed_at', "desc")
            ->orderBy('updated_at', "desc");

        if (!$special || $special != 'shared') {
            $concepts->where('owner_id', Auth::id());
        }

        if ($request->has('tag')) {
            $concepts->withAllTags([$request->input('tag')]);
            $page_title .= ' with tag "' . $request->input('tag') . '"';
        }

        $search_term = '';

        // https://dev.mysql.com/doc/refman/5.7/en/fulltext-query-expansion.html

        if ($request->has('q')) {
            $search_term = $request->input('q');
            $concepts->whereRaw(
                'MATCH(title,summary,body) AGAINST(? IN NATURAL LANGUAGE MODE)', [$search_term]
            );
        }

        // jquery-ui.autocomplete
        if ($request->has('term')) {
            $search_term = $request->input('term');
            $concepts->whereRaw(
                'MATCH(title,summary,body) AGAINST(? IN NATURAL LANGUAGE MODE)', [$search_term]
            );
        }

        if ($request->has('except')) {
            $concepts->where('id', '!=', $request->input('except'));
        }

        if ($request->has('limit')) {
            $concepts->limit((int)$request->input('limit'));
        }

        if ($request->format() == 'json') {
            $items = $concepts
                ->select('id', 'title', 'parent_id', '_lft', '_rgt')
                ->with('ancestors')
                ->paginate()
                ->appends($request->except(['page']));

            $items->each(function (Concept $item, $key) {
                $item->path = $item->ancestors->count()
                    ? ('/' . implode('/', $item->ancestors->pluck('title')->toArray()))
                    : '';
                $item->path .= '/' . $item->title;
            });

            return response()->json($items);
        }
        else {
            $result = $concepts
                ->paginate()
                ->appends($request->except(['page']));

            return view('concept.index', [
                'concepts' => $result,
                'page_title' => $page_title,
                'sub_title' => $result->firstItem() . ' &hellip; ' . $result->lastItem() . ' of ' . $result->total(),
                'search_term' => $search_term,
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $concept = new Concept([
            'weight' => 0,
            'uuid' => Uuid::uuid1()->toString(),
        ]);

        if ($request->has('parent_id')) {
            $parent = Concept::findOrFail($request->input('parent_id'));
            $this->authorize('view', $parent);
            $concept->appendToNode($parent);
        }

        return view('concept.create', [
            'concept' => $concept,
        ]);
    }

    private function registerObserver($type)
    {
        if ($type != 'concept') {
            $observer = "\\Knowfox\\Observers\\" . ucfirst($type) . "Observer";
            if (class_exists($observer)) {
                Concept::observe($observer);
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  ConceptRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ConceptRequest $request)
    {
        $this->registerObserver($request->input('type'));

        $concept = new Concept($request->all());

        $concept->owner_id = $request->user()->id;

        $concept->is_flagged = $request->has('is_flagged');

        DB::transaction(function () use ($concept, $request) {
            $concept->save();

            if ($request->has('tags')) {
                $concept->tag($request->input('tags'));
            }
            else {
                $concept->untag();
            }
        });

        return redirect()->route('concept.show', [$concept])
            ->with('status', 'Concept created');
    }

    /**
     * Display the specified resource.
     *
     * @param  Concept  $concept
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Concept $concept)
    {
        $this->authorize('view', $concept);

        $concept->load('related', 'inverseRelated', 'tagged', 'shares');

        $view_name = 'concept.show';
        if ($concept->type != 'concept') {

            $view_name = 'concept.show';

            $scoped_type = preg_split('/:\s*/', $concept->type, 2);
            if (count($scoped_type) == 1) {
                $type = $scoped_type[0];
            }
            else {
                $package = $scoped_type[0];
                $type = $scoped_type[1];
                $view_name = $package . '::show';
            }

            $suffix = '-' . preg_replace('/\W+/', '-', $type);
            if (View::exists($view_name . $suffix)) {
                $view_name .= $suffix;
            }
        }

        $concept->viewed_at = strftime('%Y-%m-%d %H:%M:%S');
        $concept->viewed_count += 1;
        $concept->timestamps = false;
        $concept->save();

        return view($view_name, [
            'page_title' => $concept->title,
            'uuid' => $concept->uuid,
            'concept' => $concept,
            'is_owner' => $concept->owner_id == $request->user()->id,
            'can_update' => $request->user()->can('update', $concept),
            'children' => $concept->getPaginatedChildren($request->letter),
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
        $this->registerObserver($request->input('type'));

        $concept->fill($request->all());

        if (!$request->has('parent_id')) {
            $concept->makeRoot();
        }

        $concept->is_flagged = $request->has('is_flagged');

        DB::transaction(function () use ($concept, $request) {
            $concept->save();

            if ($request->has('tags')) {
                $concept->retag($request->input('tags'));
            }
            else {
                $concept->untag();
            }
        });

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

        $title = "#{$concept->id} \"{$concept->title}\"";
        $parent_id = $concept->getParentId();

        $concept->delete();

        if ($parent_id) {
            return redirect()->route('concept.show', [$parent_id])
                ->with('status', 'Concept ' . $title . ' deleted');
        }
        else {
            return redirect()->route('concept.index')
                ->with('status', 'Concept ' . $title . ' deleted');
        }
    }

    public function image(PictureService $picture, Request $request, Concept $concept, $filename)
    {
        $this->authorize('view', $concept);

        $args = [];
        if ($request->has('style')) {
            $style = $request->input('style');
        }
        else
        if ($request->has('width')) {
            $style = 'width';
            $args[] = $request->input('width');
        }
        else {
            $style = 'original';
        }
        return $picture->image($concept->uuid, $filename, $style, $args);
    }

    public function upload(PictureService $picture, Request $request, $uuid)
    {
        $concept = Concept::where('uuid', $uuid)->firstOrFail();
        $this->authorize('update', $concept);

        $path = $picture->upload($request->file('file'), $uuid);

        $file = new File($path);
        if (strpos($file->getMimeType(), 'image/') === 0) {
            $parts = pathinfo($path);

            if (strpos($concept->body, $parts['basename']) === false) {
                $concept->body .= "\n\n<a data-featherlight=\"{$parts['basename']}\">![{$parts['filename']}]({$parts['basename']}?style=square)</a>\n";
                $concept->save();
            }
        }

        return response()->json(['success' => $path]);
    }

    public function images(PictureService $picture, Concept $concept)
    {
        $this->authorize('view', $concept);
        return response()->json($picture->images($concept->uuid));
    }

    public function slides(Concept $concept)
    {
        $this->dispatch(new PublishPresentation($concept));

        $url = '/presentation/' . str_replace('-', '/', $concept->uuid) . '/index.html';

        return back()
            ->with('status', '<a href="' . $url . '">Slides</a> generated');
    }

    public function versions(Request $request, Concept $concept)
    {
        return view('concept.versions', [
            'concept' => $concept,
            'is_owner' => $concept->owner_id == $request->user()->id,
            'can_update' => $request->user()->can('update', $concept),
        ]);
    }

    public function uuid($uuid)
    {
        $concept = Concept::where('uuid', $uuid)->firstOrFail();
        return redirect()->route('concept.show', [$concept]);
    }
}
