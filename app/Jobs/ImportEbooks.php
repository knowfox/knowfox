<?php

namespace Knowfox\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Support\Facades\Config;
use Knowfox\Models\ImportedEbook;
use Knowfox\Models\Concept;


class ImportEbooks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;
    private $sqlitedb;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $sqlitedb)
    {
        $this->user = $user;
        $this->sqlitedb = $sqlitedb;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Config::set('database.connections.sqlite.database', $this->sqlitedb);

        $root = Concept::whereIsRoot()->where('title', 'Books')->first();
        if (!$root) {
            $this->error('No "Books" root');
            return;
        }

        $ebooks = ImportedEbook::all();
        foreach ($ebooks as $ebook) {
            $title = empty($ebook->title) ? $ebook->filename : $ebook->title;

            echo $title, "\n";

            $concept = Concept::firstOrNew([
                'parent_id' => $root->id,
                'title' => $title,
                'owner_id' => $this->user->id,
            ]);

            $concept->config = [
                'author' => $ebook->author,
                'publisher' => $ebook->publisher,
                'year' => $ebook->year,
                'filename' => $ebook->filename,
                'path' => $ebook->path,
                'type' => $ebook->type,
                'format' => $ebook->format,
            ];

            $concept->type = 'ebook';
            $concept->created_at = $ebook->created_at;
            $concept->updated_at = $ebook->updated_at;

            $concept->save();

            if (!empty($ebook->tags)) {
                $concept->retag(preg_split('/\s+/', $ebook->tags));
            }
        }
    }
}
