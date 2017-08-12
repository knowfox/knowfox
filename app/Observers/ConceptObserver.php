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

    /**
     * Parse items from the concept body
     * @param Concept $concept
     */
    public function saving(Concept $concept)
    {
        if (!$concept->timestamps) {
            return;
        }

        $concept->items()->delete();

        if (!$concept->body) {
            return;
        }
        if (!preg_match_all('/^\s*\*\s+(\[( |x|X)\]\s*(.*))$/m',
            $concept->body, $lines, PREG_SET_ORDER)) {

            return;
        }

        $parser = new GithubMarkdown();
        $parser->html5 = true;

        $concept_tags = $concept->tags->filter(function ($tag) {
            return $tag->name != 'Journal';
        })->map(function ($tag) {
            return $tag->name;
        })->toArray();

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

            preg_match_all('/#(\S+)/', $title, $matches, PREG_PATTERN_ORDER);
            $title = trim(preg_replace('/\s*#\S+/', '', $title));

            $item = Item::firstOrCreate([
                'concept_id' => $concept->id,
                'title' => $parser->parse($title),
                'owner_id' => $concept->owner_id,
            ], [
                'is_done' => $is_done,
                'due_at' => $due_at,
            ]);
            $item->retag(array_merge($concept_tags, $matches[1]));
        }
    }
}