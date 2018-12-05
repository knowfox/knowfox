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

use Conner\Tagging\Model\Tag;
use Conner\Tagging\Model\Tagged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function cloud(Request $request)
    {
        $order = $request->input('order', 'count') == 'count' ? 'count' : 'name';
        $dir = $request->input('dir', 'desc') == 'desc' ? 'desc' : 'asc';

        $tags = Tagged::select(DB::raw("COUNT(tagging_tagged.id) AS count, tag_name AS name, tag_slug AS slug"))
            ->leftJoin('concepts', function ($join) {
                $join
                    ->on('concepts.id', '=', 'tagging_tagged.taggable_id');
            })
            ->groupBy('tag_name', 'tag_slug')
            ->where('taggable_type', '=', 'Knowfox\\Models\\Concept')
            ->where('concepts.owner_id', '=', Auth::id())
            ->orderBy($order, $dir)
            ->paginate()
            ->appends($request->only(['order', 'dir']));

        return view('tag.cloud', [
            'page_title' => 'Tags',
            'sub_title' => $tags->firstItem() . ' &hellip; ' . $tags->lastItem() . ' of ' . $tags->total(),
            'tags' => $tags,
            'order' => $order,
            'dir_icon' => $dir == 'desc' ? 'sort-by-attributes-alt' : 'sort-by-attributes',
            'other_dir' => $dir == 'desc' ? 'asc' : 'desc',
        ]);
    }
}
