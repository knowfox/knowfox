<?php

namespace Knowfox\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Knowfox\Models\Concept;

use Knowfox\Services\OutlineService;
use Knowfox\Services\PictureService;


class PublishWebsite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $domain_concept;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $domain_name)
    {
        $root = Concept::whereIsRoot()
            ->where('owner_id', $user->id)
            ->where('title', 'Websites')->firstOrFail();
        $this->domain_concept = Concept::where('parent_id', $root->id)
            ->where('owner_id', $user->id)
            ->where('title', $domain_name)
            ->firstOrFail();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PictureService $picture, OutlineService $outline)
    {

    }
}
