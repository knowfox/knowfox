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

    public function cloud()
    {
        $tags = Tag::orderBy('count', 'desc')->paginate();
        return view('tag.cloud', [
            'page_title' => 'Tags',
            'sub_title' => $tags->firstItem() . ' &hellip; ' . $tags->lastItem() . ' of ' . $tags->total(),
            'tags' => $tags
        ]);
    }
}
