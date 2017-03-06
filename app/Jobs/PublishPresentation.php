<?php

namespace Knowfox\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Knowfox\Models\Concept;
use Knowfox\Services\OutlineService;
use DOMDocument;
use DOMXpath;
use Exception;
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
     * @param $el \DOMElement
     */
    private function getUuid($el)
    {
        while (($el = $el->parentNode) && !$el->hasAttribute('data-uuid'));
        return $el->getAttribute('data-uuid');
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

        libxml_use_internal_errors(true);
        $dom = new DOMDocument;

        // @see http://de1.php.net/manual/en/domdocument.loadhtml.php
        if (!$dom->loadHTML('<?xml version="1.0" encoding="UTF-8" ?>' . $markup)) {
            $messages = [];
            foreach(libxml_get_errors() as $error) {
                $messages[] = $error->message;
            }
            throw new Exception("XML: " . join(', ', $messages));
        }

        $xpath = new DOMXpath($dom);

        foreach (iterator_to_array($xpath->query('//img')) as $i => $image) {
            $url = parse_url(trim($image->getAttribute('src')));
            if (!empty($url['host']) && $url['host'] != 'knowfox.com') {
                continue;
            }

            if (preg_match('#^(/concept)?/(\d+)/(.*)#', $url['path'], $matches)) {
                $uuid = Concept::find($matches[2])->uuid;
                $filename = $matches[3];
            }
            else {
                $uuid = $this->getUuid($image);
                $filename = $url['path'];
            }

            $style = 'original';
            $args = [];
            if (!empty($url['query'])) {
                $query = [];
                parse_str($url['query'], $query);

                if (isset($query['width'])) {
                    $style = 'width';
                    $args[] = $query['width'];
                }
                else
                if (isset($query['style'])) {
                    $style = $query['style'];
                }
            }

            $path = $this->directory . '/' . $filename;
            file_put_contents(
                $path,
                $picture->imageData($path, $style, $args)
            );

            $image->setAttribute('src', $filename);
        }

        $text = '';
        $html = $dom->getElementsByTagName('html')->item(0);
        foreach ($html->childNodes as $node) {
            $text .= $dom->saveHTML($node);
        }

        file_put_contents($this->directory . '/index.html', $text);
    }
}
