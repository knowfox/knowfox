<?php

namespace Knowfox\Console\Commands;

use EDAM\NoteStore\NoteFilter;
use EDAM\NoteStore\NotesMetadataResultSpec;
use Evernote\Client;
use Illuminate\Console\Command;
use Knowfox\Models\Concept;

class ImportEvernote extends Command
{
    const OWNER_ID = 1;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evernote:import {notebook}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import concepts from Evernote';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function importNote($notebook_concept, $note)
    {
        $year = date('Y', $note->created / 1000);
        $year_concept = Concept::firstOrCreate([
            'parent_id' => $notebook_concept->id,
            'title' => $year,
            'owner_id' => self::OWNER_ID,
        ]);

        $month = date('m', $note->created / 1000);
        $month_concept = Concept::firstOrCreate([
            'parent_id' => $year_concept->id,
            'title' => $month,
            'owner_id' => self::OWNER_ID,
        ]);

        $concept = Concept::firstOrNew([
            'uuid' => $note->guid,
        ]);

        $concept->parent_id = $month_concept->id;
        $concept->title = $note->title;
        $concept->source_url = $note->attributes->sourceURL;
        $concept->owner_id = self::OWNER_ID;
        $concept->created_at = strftime('%Y-%m-%d %H:%M:%S', $note->created / 1000);
        $concept->updated_at = strftime('%Y-%m-%d %H:%M:%S', $note->updated / 1000);
        $concept->save();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $notebook_name = $this->argument('notebook');

        $token = env('EVERNOTE_DEVTOKEN');

        $client = new Client([
            'token' => $token,
            'sandbox' => false
        ]);
        $store = $client->getNoteStore();

        $notebooks = $store->listNotebooks();
        $this->info("Found " . count($notebooks) . " notebooks");

        $found = false;
        foreach ($notebooks as $notebook) {
            if ($notebook->name == $notebook_name) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $this->error('No such notebook');
        }
        $filter = new NoteFilter([
            'notebookGuid' => $notebook->guid,
        ]);

        $spec = new NotesMetadataResultSpec();
        $spec->includeTitle = TRUE;
        $spec->includeCreated = TRUE;
        $spec->includeUpdated = TRUE;
        $spec->includeDeleted = TRUE;

        $counts = $store->findNoteCounts($token, $filter, false);
        $count = $counts->notebookCounts[$notebook->guid];

        $root = Concept::whereIsRoot()->where('title', 'Evernote')->first();
        if (!$root) {
            $this->error('No "Journal" root');
            return;
        }

        $notebook_concept = Concept::firstOrNew([
            'parent_id' => $root->id,
            'title' => $notebook->name,
            'uuid' => $notebook->guid
        ]);
        $notebook_concept->owner_id = self::OWNER_ID;
        $notebook_concept->save();

        $page_size = 10;
        for ($page = 0; $page < $count / $page_size; $page++) {
            $offset = $page * $page_size;
            $next_offset = min(($page + 1) * $page_size, $count);
            $batch_size = $next_offset - $offset;

            $this->info('Page ' . $page . ': ' . $offset . ' .. ' . ($next_offset - 1) . ', batch: ' . $batch_size);

            $notes = $store->findNotesMetadata($token, $filter, $offset, $batch_size, $spec);
            foreach ($notes->notes as $note_proxy) {
                $this->info(' * ' . $note_proxy->title);

                $note = $store->getNote(
                    $token,
                    $note_proxy->guid,
                    true, // withContent
                    true, // withResourcesData
                    false, // withResourcesRecognition
                    false // withResourcesAlternateData
                );
                $note->tagNames = $store->getNoteTagNames($token, $note_proxy->guid);

                $this->importNote($notebook_concept, $note);
            }
        }

    }
}
