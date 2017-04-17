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

class PublishPresentation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $concept;
    private $directory;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Concept $concept)
    {
        $this->concept = $concept;
        $this->directory = public_path('presentation')
            . '/' . str_replace('-', '/', $concept->uuid);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PictureService $picture, OutlineService $outline)
    {
        @mkdir($this->directory, 0755, true);

        $markup = $outline->renderDescendents(
            $this->concept, 'presentation.layout', 'presentation.slides'
        );

        @symlink(base_path('node_modules/reveal/index.css'), $this->directory . '/index.css');
        @symlink(base_path('node_modules/reveal/index.js'), $this->directory . '/index.js');
        @symlink(base_path('node_modules/reveal/theme'), $this->directory . '/theme');

        $markup = $picture->extractPictures($markup, $this->directory);
        file_put_contents($this->directory . '/index.html', $markup);
    }
}
