<?php

namespace Knowfox\Jobs;

use Carbon\Carbon;
use Doctrine\DBAL\Driver\PDOException;
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

    protected $notebook_name;
    protected $user;
    protected $picture;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $notebook_name)
    {
        $this->user = $user;
        $this->notebook_name = $notebook_name;
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

    private function replaceInMarkup($markup, $xpath_expression, $callback)
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;

        // @see http://de1.php.net/manual/en/domdocument.loadhtml.php

        $wrapped_markup = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>';
        if (strpos($markup, '<en-note') !== false) {
            $wrapped_markup .= $markup;
        }
        else {
            $wrapped_markup .= '<en-note>' . $markup . '</en-note>';
        }

        if (!$dom->loadHTML($wrapped_markup)) {
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
        foreach ($xpath->query($xpath_expression) as $el) {
            $callback($el, $dom);
        }

        $text = '';
        $html = $dom->getElementsByTagName('en-note')->item(0);
        foreach ($html->childNodes as $node) {
            $text .= $dom->saveHTML($node);
        }
        return $text;
    }

    public function replaceMedia($markup, $attachments)
    {
        $importer = $this;

        return $this->replaceInMarkup($markup, '//en-media',
            function ($el, $dom) use ($attachments, $importer)
            {
                $type = $el->getAttribute('type');
                $hash = $el->getAttribute('hash');

                $importer->info("   - Replacing {$type} {$hash}");

                if (strpos($type, 'image/') === 0) {
                    $replacement = $dom->createElement('img');

                    $filename = $attachments[$hash];
                    $width = $el->getAttribute('width');

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
                $el->parentNode->replaceChild($replacement, $el);
            }
        );
    }

    public function replaceLinks($markup)
    {
        $importer = $this;

        return $this->replaceInMarkup($markup, '//a',
            function ($el, $dom) use ($importer)
            {
                $importer->info("   - Replacing links");

                // Links, e.g. evernote:///view/84898/s1/f38be1a9-ed57-44c6-9788-56f94414a976/f38be1a9-ed57-44c6-9788-56f94414a976/
                $href = $el->getAttribute('href');
                if (!preg_match('#^evernote:///view/\d+/[^/]+/([^/]+)/#', $href, $matches)) {
                    return;
                }
                $uuid = $matches[1];

                $replacement = $dom->createElement('a');
                $replacement->setAttribute('href', "/uuid/{$uuid}");
                $replacement->appendChild(
                    $dom->createTextNode($el->nodeValue)
                );
                $el->parentNode->replaceChild($replacement, $el);
            }
        );
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

        $concept->body = $this->replaceLinks($note->content);
        $concept->body = $this->replaceMedia($concept->body, $attachments);

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

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PictureService $picture)
    {
        $this->picture = $picture;
        $token = env('EVERNOTE_DEVTOKEN');

        $count = 0;
        $page = 0;
        $page_count = 0;

        try {
            $client = new Client([
                'token' => $token,
                'sandbox' => false
            ]);
            $store = $client->getNoteStore();

            $notebooks = $store->listNotebooks();
            $this->info("Found " . count($notebooks) . " notebooks");

            $found = false;
            foreach ($notebooks as $notebook) {
                if ($notebook->name == $this->notebook_name) {
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
            $page_count = (int)ceil($count / self::PAGE_SIZE);

            $root = Concept::whereIsRoot()->where('title', 'Evernote')->first();
            if (!$root) {
                $this->error('No "Evernote" root');
                return;
            }

            $notebook_concept = Concept::firstOrNew([
                'parent_id' => $root->id,
                'title' => $notebook->name,
                'uuid' => $notebook->guid
            ]);
            $notebook_concept->owner_id = $this->user->id;
            $notebook_concept->save();

            for ($page = 0; $page < $page_count; $page++) {
                $offset = $page * self::PAGE_SIZE;
                $next_offset = min(($page + 1) * self::PAGE_SIZE, $count);
                $batch_size = $next_offset - $offset;

                $this->info('Page ' . $page . '/' . $page_count . ': ' . $offset . ' .. ' . ($next_offset - 1) . ', batch: ' . $batch_size);

                $notes = $store->findNotesMetadata($token, $filter, $offset, $batch_size, $spec);

                foreach ($notes->notes as $note_proxy) {
                    $this->info(' * ' . $note_proxy->title . ' (' . $node_proxy-> created. ', uuid: ' . $node_proxy->guid . ')');

                    $concept = Concept::with('tagged')->firstOrNew([
                        'uuid' => $note_proxy->guid,
                    ]);

                    if (!empty($concept->updated_at) && strtotime($concept->updated_at) <= $note_proxy->updated / 1000) {
                        $this->info('   - skipped');
                        continue;
                    }

                    try {
                        $note = $store->getNote(
                            $token,
                            $note_proxy->guid,
                            true, // withContent
                            true, // withResourcesData
                            false, // withResourcesRecognition
                            false // withResourcesAlternateData
                        );
                        $note->tagNames = $store->getNoteTagNames($token, $note_proxy->guid);

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
                    catch (PDOException $e)
                    {
                        $msg = $e->getMessage();
                        $this->error("{$concept->title}: " . $msg);
                    }
                }
            }
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
            dispatch(new SendImportCompleteMail($this->user, $this->notebook_name, $info));
        }

        $info = [
            'status' => 'COMPLETE',
            'count' => $count,
            'page' => $page,
            'page_count' => $page_count,
        ];

        dispatch(new SendImportCompleteMail($this->user, $this->notebook_name, $info));
    }
}
