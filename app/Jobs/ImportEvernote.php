<?php

namespace Knowfox\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Str;

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeExtensionGuesser;

use EDAM\Error\EDAMErrorCode;
use EDAM\Error\EDAMSystemException;
use EDAM\NoteStore\NoteFilter;
use EDAM\NoteStore\NotesMetadataResultSpec;
use Evernote\Client;

use Knowfox\User;
use Knowfox\Models\Concept;
use Knowfox\Services\PictureService;

use DOMDocument;
use DOMXpath;

class ImportEvernote implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const PAGE_SIZE = 100;

    protected $scope;
    protected $notebook;
    protected $note;
    protected $user;
    protected $picture;
    protected $token;

    /**
     * Create a new job instance.
     *
     * @param $scope notebook_name | notebook_uuid | note_uuid
     * @param $selector notebook_name | [ 'notebook_uuid' => 123, 'note_uuid' => 456 ]
     *
     * @return void
     */
    public function __construct(User $user, $scope, $notebook, $note = null)
    {
        $this->user = $user;
        $this->scope = $scope;
        $this->notebook = $notebook;
        $this->note = $note;
    }

    private function log($what, $txt)
    {
        error_log(strftime("[%Y-%m-%d %H:%M:%S] {$what}: {$txt}\n"), 3, "/tmp/knowfox.log");
    }

    private function info($txt)
    {
        $this->log('info', $txt);
    }

    private function error($txt)
    {
        $this->log('error', $txt);
    }

    private function replaceMedia($markup, $attachments)
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;

        // @see http://de1.php.net/manual/en/domdocument.loadhtml.php
        if (!$dom->loadHTML('<?xml version="1.0" encoding="UTF-8" ?>' . $markup)) {
            $messages = [];
            foreach(libxml_get_errors() as $error) {
                $messages[] = $error->message;
            }
            throw new \Exception("XML: " . join(', ', $messages));
        }

        /* Does not find all en-media
           foreach ($dom->getElementsByTagName('en-media') as $i => $media) {
         */
        $xpath = new DOMXpath($dom);
        foreach ($xpath->query('//en-media') as $i => $media) {

            $type = $media->getAttribute('type');
            $hash = $media->getAttribute('hash');

            $this->info("   - Replacing {$type} {$hash}");

            if (strpos($type, 'image/') === 0) {
                $replacement = $dom->createElement('img');

                $filename = $attachments[$hash];
                $width = $media->getAttribute('width');

                if ($width) {
                    $filename .= '?width=' . $width;
                }

                $replacement->setAttribute('src', $filename);
            }
            else {
                $replacement = $dom->createElement('a');
                $replacement->setAttribute('href', $attachments[$hash]);
                $replacement->appendChild(
                    $dom->createTextNode($attachments[$hash])
                );
            }
            $media->parentNode->replaceChild($replacement, $media);
        }

        $text = '';
        $html = $dom->getElementsByTagName('en-note')->item(0);
        foreach ($html->childNodes as $node) {
            $text .= $dom->saveHTML($node);
        }
        return $text;
    }

    protected function importNote($notebook_concept, $concept, $note)
    {
        $year = date('Y', $note->created / 1000);
        $year_concept = Concept::firstOrCreate([
            'parent_id' => $notebook_concept->id,
            'title' => $year,
            'owner_id' => $this->user->id,
        ]);

        $month = date('m', $note->created / 1000);
        $month_concept = Concept::firstOrCreate([
            'parent_id' => $year_concept->id,
            'title' => $month,
            'owner_id' => $this->user->id,
        ]);

        $concept->parent_id = $month_concept->id;
        $concept->title = $note->title;

        /*
         * Debugging
         *
        if ($note->title == 'PICAXE-Projekte') {
            $this->info("PICAXE-Projekte");
        }
         */
        $concept->source_url = $note->attributes->sourceURL;
        $concept->owner_id = $this->user->id;
        $concept->created_at = strftime('%Y-%m-%d %H:%M:%S', $note->created / 1000);
        $concept->updated_at = strftime('%Y-%m-%d %H:%M:%S', $note->updated / 1000);

        $attachments = [];
        if ($note->resources) {
            foreach ($note->resources as $resource) {
                $hash = bin2hex($resource->data->bodyHash);
                $filename = Str::slug($resource->attributes->fileName);
                if (!$filename || strlen($filename) > 100) {
                    $guesser = new MimeTypeExtensionGuesser();
                    $filename = $hash . '.' . $guesser->guess($resource->mime);
                }

                $attachments[$hash] = $filename;

                $this->info("   - Saving {$filename} {$hash}");

                $directory = $this->picture->imageDirectory($concept->uuid);

                @mkdir($directory, 0755, true);
                file_put_contents($directory . '/' . $filename, $resource->data->body);
            }
        }

        $concept->body = $this->replaceMedia($note->content, $attachments);

        $concept->save();

        $concept->retag($note->tagNames);
    }

    private function getErrorDetails($e)
    {
        $details = '';
        // @see https://dev.evernote.com/doc/articles/rate_limits.php
        if ($e->errorCode == EDAMErrorCode::RATE_LIMIT_REACHED) {
            $details = sprintf(': Rate limit reached. Retry in %0.2d min', $e->rateLimitDuration / 60);
        }
        return $details;
    }

    protected function findNotebook($store)
    {
        $notebooks = $store->listNotebooks();
        $this->info("Found " . count($notebooks) . " notebooks");

        $found = false;
        foreach ($notebooks as $notebook) {
            switch ($this->scope)
            {
                case 'notebook_name':
                    if ($notebook->name == $this->notebook) {
                        $found = true;
                        break;
                    }

                case 'notebook_uuid':
                case 'note_uuid':
                    if ($notebook->guid == $this->notebook) {
                        $found = true;
                        break;
                    }
            }
        }
        if (!$found) {
            $this->error('No such notebook');
            return null;
        }

        $root = Concept::whereIsRoot()->where('title', 'Evernote')->first();
        if (!$root) {
            $this->error('No "Evernote" root');
            return null;
        }

        $notebook_concept = Concept::firstOrNew([
            'parent_id' => $root->id,
            'title' => $notebook->name,
            'uuid' => $notebook->guid
        ]);
        $notebook_concept->owner_id = $this->user->id;
        $notebook_concept->save();

        return $notebook_concept;
    }

    protected function importNotebook($store)
    {
        $notebook_concept = $this->findNotebook($store);
        if (!$notebook_concept) {
            return;
        }

        /*
         * Handle a single note
         */
        if ($this->scope == 'note_uuid') {
            $concept = Concept::with('tagged')->firstOrNew([
                'uuid' => $this->note,
            ]);

            try {
                $note = $store->getNote(
                    $this->token,
                    $this->note,
                    true, // withContent
                    true, // withResourcesData
                    false, // withResourcesRecognition
                    false // withResourcesAlternateData
                );
                $note->tagNames = $store->getNoteTagNames($this->token, $this->note);

                $this->importNote($notebook_concept, $concept, $note);
            }
            catch (EDAMSystemException $e) {
                $details = $this->getErrorDetails($e);
                $this->error("{$concept->title}: " . $details);

                if ($e->errorCode == EDAMErrorCode::RATE_LIMIT_REACHED) {
                    throw $e;
                }
            }

            $info = [
                'status' => 'NOTE',
                'note' => $this->note,
            ];

            dispatch(new SendImportCompleteMail($this->user, $this->scope . ':' . $this->notebook, $info));
            
            return;
        }

        /*
         * No single note was specified. Import the whole notebook, paginated
         */
        $spec = new NotesMetadataResultSpec();
        $spec->includeTitle = TRUE;
        $spec->includeCreated = TRUE;
        $spec->includeUpdated = TRUE;
        $spec->includeDeleted = TRUE;

        $filter = new NoteFilter([
            'notebookGuid' => $notebook_concept->uuid,
        ]);

        $counts = $store->findNoteCounts($this->token, $filter, false);
        $count = $counts->notebookCounts[$notebook_concept->uuid];
        $page_count = (int)ceil($count / self::PAGE_SIZE);

        for ($page = 0; $page < $page_count; $page++) {
            $offset = $page * self::PAGE_SIZE;
            $next_offset = min(($page + 1) * self::PAGE_SIZE, $count);
            $batch_size = $next_offset - $offset;

            $this->info('Page ' . $page . '/' . $page_count . ': ' . $offset . ' .. ' . ($next_offset - 1) . ', batch: ' . $batch_size);

            $notes = $store->findNotesMetadata($this->token, $filter, $offset, $batch_size, $spec);

            foreach ($notes->notes as $note_proxy) {
                $this->info(' * ' . $note_proxy->title);

                $concept = Concept::with('tagged')->firstOrNew([
                    'uuid' => $note_proxy->guid,
                ]);

                if (!empty($concept->updated_at) && strtotime($concept->updated_at) <= $note_proxy->updated / 1000) {
                    $this->info('   - skipped');
                    continue;
                }

                try {
                    $note = $store->getNote(
                        $this->token,
                        $note_proxy->guid,
                        true, // withContent
                        true, // withResourcesData
                        false, // withResourcesRecognition
                        false // withResourcesAlternateData
                    );
                    $note->tagNames = $store->getNoteTagNames($this->token, $note_proxy->guid);

                    $this->importNote($notebook_concept, $concept, $note);
                }
                catch (EDAMSystemException $e) {
                    $details = $this->getErrorDetails($e);
                    $this->error("{$concept->title}: " . $details);

                    if ($e->errorCode == EDAMErrorCode::RATE_LIMIT_REACHED) {
                        throw $e;
                    }
                    continue;
                }
            }
        }

        $info = [
            'status' => 'COMPLETE',
            'count' => $count,
            'page' => $page,
            'page_count' => $page_count,
        ];

        dispatch(new SendImportCompleteMail($this->user, $this->scope . ':' . $this->notebook, $info));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PictureService $picture)
    {
        $this->picture = $picture;
        $this->token = env('EVERNOTE_DEVTOKEN');

        try {
            $client = new Client([
                'token' => $this->token,
                'sandbox' => false
            ]);
            $store = $client->getNoteStore();
            $this->importNotebook($store);
        }
        catch (EDAMSystemException $e) {
            $details = $this->getErrorDetails($e);
            $this->error("System exception" . $details);

            if ($e->errorCode == EDAMErrorCode::RATE_LIMIT_REACHED) {
                $job = (new ImportEvernote($this->user, $this->notebook_name))
                    ->delay(Carbon::now()->addSeconds($e->rateLimitDuration));
                dispatch($job);
            }

            $info = [
                'status' => 'ERROR',
                'count' => $count,
                'page' => $page,
                'page_count' => $page_count,
                'message' => $details,
            ];
            dispatch(new SendImportCompleteMail($this->user, $this->scope . ':' .  $this->notebook, $info));
        }
    }
}
