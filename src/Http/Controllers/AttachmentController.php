<?php

namespace Knowfox\Http\Controllers;

use Knowfox\Models\Attachment;
use Illuminate\Http\Request;
use Knowfox\Models\Concept;

class AttachmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Concept $concept)
    {
        $attachments = Attachment::where('concept_id', $concept->id)
            ->whereNull('original_id')
            ->orderBy('is_default', 'desc')
            ->paginate();

        return view('knowfox::attachment.index', [
            'concept' => $concept,
            'attachments' => $attachments,
            'page_title' => 'Attachments',
            'sub_title' => $attachments->firstItem() . ' &hellip; ' . $attachments->lastItem() . ' of ' . $attachments->total(),
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
     * @param  \Knowfox\Models\Attachment  $attachment
     * @return \Illuminate\Http\Response
     */
    public function show(Attachment $attachment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Knowfox\Models\Attachment  $attachment
     * @return \Illuminate\Http\Response
     */
    public function edit(Attachment $attachment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Knowfox\Models\Attachment  $attachment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Attachment $attachment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Knowfox\Models\Attachment  $attachment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Attachment $attachment)
    {
        //
    }
}
