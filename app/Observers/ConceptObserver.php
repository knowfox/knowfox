<?php

namespace Knowfox\Observers;

use Illuminate\Support\Facades\Auth;
use Knowfox\Models\Concept;
use Knowfox\Models\Item;
use cebe\markdown\GithubMarkdown;

class ConceptObserver
{
    /**
     * Make sure the concept to be created has its owner field set,
     * eg. during outlining
     * @param \Knowfox\Models\Concept $concept
     */
    public function creating(Concept $concept)
    {
        if (empty($concept->owner_id)) {
            $concept->owner_id = Auth::id();
        }
    }

    private function extractItems($concept)
    {
        $items = $concept->items()->pluck('title', 'id')->toArray();

        if (!preg_match_all('/^\s*\*\s+(\[( |x|X)\]\s*(.*))$/m',
            $concept->body, $lines, PREG_SET_ORDER)) {

            return;
        }

        $parser = new GithubMarkdown();
        $parser->html5 = true;

        $concept_tags = empty(request()->tags) ? [] : request()->tags;
        $concept_tags = array_filter($concept_tags, function ($tag) {
            return $tag != 'journal';
        });

        foreach ($lines as $line) {
            $title = $line[3];

            $is_done = false;
            if ($line[2] != ' ') {
                $is_done = true;
            }

            $due_at = null;
            if (preg_match('/\d{4}-\d{2}-\d{2}/', $title, $match)) {
                $due_at = $match[0];
                $title = preg_replace('/\s*\d{4}-\d{2}-\d{2}/', '', $title, 1);
            }

            preg_match_all('/#(\S+)/', $title, $tag_matches, PREG_PATTERN_ORDER);
            $title = trim(preg_replace('/\s*#\S+/', '', $title));

            $title = $parser->parse($title);
            if (preg_match('#^<p>(.*)</p>\s*$#s',$title, $matches)) {
                $title = $matches[1];
            }

            $item = Item::updateOrCreate([
                'concept_id' => $concept->id,
                'title' => $title,
                'owner_id' => $concept->owner_id,
            ], [
                'is_done' => $is_done,
                'due_at' => $due_at,
            ]);

            unset($items[$item->id]);

            $item->retag(array_merge($concept_tags, $tag_matches[1]));
        }

        Item::destroy(array_keys($items));
    }

    /**
     * Parse items from the concept body
     * @param Concept $concept
     */
    public function saving(Concept $concept)
    {
        if (!$concept->timestamps) {
            return;
        }

        if (!$concept->body) {
            return;
        }

        $this->extractItems($concept);
    }
}