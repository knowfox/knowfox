<?php

namespace Knowfox\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Knowfox\Models\Circle;
use Illuminate\Http\Request;
use Knowfox\Models\Concept;

class CircleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $circles = Circle::where('owner_id', Auth::id())->paginate();
        return view('circle.index', [
            'circles' => $circles,
            'page_title' => 'Circles',
            'sub_title' => $circles->firstItem() . ' &hellip; ' . $circles->lastItem() . ' of ' . $circles->total(),
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \Knowfox\Models\Circle  $circle
     * @return \Illuminate\Http\Response
     */
    public function show(Circle $circle)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Knowfox\Models\Circle  $circle
     * @return \Illuminate\Http\Response
     */
    public function edit(Circle $circle)
    {
        return view('circle.edit', ['circle' => $circle]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Knowfox\Models\Circle  $circle
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Circle $circle)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Knowfox\Models\Circle  $circle
     * @return \Illuminate\Http\Response
     */
    public function destroy(Circle $circle)
    {
        //
    }

    public function share(Concept $concept)
    {

    }
}
