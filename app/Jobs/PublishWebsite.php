<?php

namespace Knowfox\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
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
        $domain_concept = $this->domain_concept;
        $directory = $domain_concept->config->directory;

        $website_dir = str_replace('.', '_', $domain_concept->title);

        @mkdir($directory . '/css', 755, true);
        copy(base_path('resources/views/website/' . $website_dir . '/css/blog.css'), $directory . '/css/blog.css');

        View::share('config', $domain_concept->config);

        $publish_concept = function ($rendered_concepts)
            use ($domain_concept, $directory)
            {
                foreach ($rendered_concepts as $concept) {
                    $path = $directory . '/' . $concept->concept->slug;
                    @mkdir($path, 0755, true);
                    file_put_contents($path . '/index.html', $concept->rendered);
                }
            };

        $publish_index = function ($rendered_concepts)
            use ($domain_concept, $directory, $website_dir)
            {
                $page_count = ceil(count($rendered_concepts) / 10);
                for ($page = 0; $page < $page_count; $page++) {

                    $concepts = array_slice($rendered_concepts, $page * 10, 10);

                    $path = $directory . '/index'
                        . ($page > 0 ? "-{$page}" : '')
                        . '.html';

                    if ($page == 0) {
                        $prev_page = null;
                    }
                    else
                    if ($page == 1) {
                        $prev_page = '/index';
                    }
                    else {
                        $prev_page = '/index-' . ($page - 1);
                    }
                    if ($prev_page) $prev_page .= '.html';

                    if ($page == $page_count - 1) {
                        $next_page = null;
                    }
                    else {
                        $next_page = '/index-' . ($page + 1);
                    }
                    if ($next_page) $next_page .= '.html';

                    file_put_contents(
                        $path,
                        view('website.' . $website_dir . '.index', [
                            'concepts' => $concepts,
                            'page' => $page,
                            'page_count' => $page_count,
                            'prev_page' => $prev_page,
                            'next_page' => $next_page,
                        ])
                    );
                }
            };

        $outline->traverse($this->domain_concept,
            'website.' . $website_dir . '.concept',
            $publish_concept
        );

        $outline->traverse($this->domain_concept,
            'website.' . $website_dir . '.fragment',
            $publish_index
        );
    }
}
