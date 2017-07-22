<?php

namespace Knowfox\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\View;
use Knowfox\Models\Concept;

use Knowfox\Services\PictureService;


class PublishWebsite implements ShouldQueue
{
    const PAGE_SIZE = 3;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $domain_concept;

    /** @var PictureService $picture_service */
    protected $picture_service;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $domain_name)
    {
        $this->user = $user;

        $root = Concept::whereIsRoot()
            ->where('owner_id', $user->id)
            ->where('title', 'Websites')->firstOrFail();
        $this->domain_concept = Concept::where('parent_id', $root->id)
            ->where('owner_id', $user->id)
            ->where('title', $domain_name)
            ->firstOrFail();
    }

    private function extractImage($concept, $target_dir)
    {
        if (!empty($concept->config) && !empty($concept->config->image)) {
            $filename = $concept->slug . '/'
                . $this->picture_service->withStyle($concept->config->image, 'thumbnail');
            $target_path = $target_dir . '/' . $filename;
            $source_path =
                $this->picture_service->imageDirectory($concept->uuid) . '/'
                . $concept->config->image;
            file_put_contents(
                $target_path,
                $this->picture_service->imageData($source_path, 'thumbnail')
            );
            $concept->image = $filename;
        }
    }

    private function publishChildren($children, $show_date, $url_prefix, $website_dir, $target_dir)
    {
        $page_count = max(1, ceil(count($children) / static::PAGE_SIZE));

        $page0_fragments = '';

        for ($page = 0; $page < $page_count; $page++) {

            $children_page = $children->slice($page * static::PAGE_SIZE, static::PAGE_SIZE);
            if ($page == 0) {
                $page0_fragments = $children_page;
                continue;
            }

            $path = $target_dir . "/_page-{$page}.html";

            file_put_contents(
                $path,

                view ('website.' . $website_dir . '.fragments', [
                    'show_date' => $show_date,
                    'concepts' => $children_page,
                    'url_prefix' => $url_prefix,
                    'fragment_view' => 'website.' . $website_dir . '.fragment',
                ])->render()
            );
        }

        return $page0_fragments;
    }

    private function publishConcept($concept, $url_prefix, $breadcrumbs, $website_dir, $target_dir)
    {
        $this->extractImage($concept, $target_dir);

        $title = count($breadcrumbs) > 0 ? $concept->title : $this->domain_concept->config->title;

        array_push($breadcrumbs, (object)[
            'title' => $title,
            'url' => "{$url_prefix}/",
        ]);

        $by_date = empty($concept->config->sort) || $concept->config->sort == 'date';

        if ($by_date) {
            $children = $concept->children()->orderBy('created_at', 'desc')->get();
        }
        else {
            $children = $concept->children()->defaultOrder()->get();
        }

        $prev = null;

        foreach ($children as $child) {
            $child->url = "{$url_prefix}/{$child->slug}/";
            $child->prev = $prev;
            $child->next = null;

            if ($prev) {
                $prev->next = $child;
            }
            $prev = $child;
        }

        foreach ($children as $child) {
            // Publish child
            $path = $target_dir . '/' . $child->slug;
            @mkdir($path, 0755, true);

            $this->publishConcept($child, "{$url_prefix}/{$child->slug}", $breadcrumbs, $website_dir, $path);
        }

        array_pop($breadcrumbs);

        // Endless scrolling. Show the first PAGE_SIZE children directly
        $children = $children->filter(function ($concept) {
            return in_array('Post', $concept->tagNames());
        });

        $page0_children =  view ('website.' . $website_dir . '.fragments', [
            'show_date' => $by_date,
            'concepts' => $this->publishChildren($children, $by_date, $url_prefix, $website_dir, $target_dir),
            'url_prefix' => $url_prefix,
            'fragment_view' => 'website.' . $website_dir . '.fragment',
        ])->render();

        file_put_contents($target_dir . '/index.html',
            view( 'website.' . $website_dir . '.concept', [
                'page_title' => $title,
                'rendered_body' => $this->picture_service->extractPictures($concept->rendered_body, $target_dir),
                'concept' => $concept,
                'breadcrumbs' => array_slice($breadcrumbs, 1),
                'url_prefix' => $url_prefix,
                'children' => $page0_children,
                'page_count' => max(1, ceil(count($children) / static::PAGE_SIZE)),
            ])->render()
        );
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PictureService $picture_service)
    {
        app('auth')->setUser($this->user);

        $this->picture_service = $picture_service;

        $domain_concept = $this->domain_concept;
        $target_dir = $domain_concept->config->directory;

        $website_dir = str_replace('.', '_', $domain_concept->title);

        @mkdir($target_dir . '/css', 0755, true);
        copy(base_path('resources/views/website/' . $website_dir . '/css/blog.css'), $target_dir . '/css/blog.css');

        View::share('config', $domain_concept->config);

        $breadcrumbs = [];

        $this->publishConcept($domain_concept, '', $breadcrumbs, $website_dir, $target_dir);
    }
}
