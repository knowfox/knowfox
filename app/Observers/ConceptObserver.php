<?php

namespace Knowfox\Observers;

use Illuminate\Support\Facades\Auth;
use Knowfox\Models\Concept;
use Knowfox\Models\Item;
use cebe\markdown\GithubMarkdown;
use Knowfox\Models\Relationship;

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

    private function syncRelated($concept, $type, $ids)
    {
        $old_due = $concept->related()
            ->wherePivot('type', $type)
            ->pluck('concepts.title', 'concepts.id')
            ->toArray();

        foreach ($ids as $id) {
            if (!isset($old_due[$id])) {
                $concept->related()->attach($id, [ 'type' => $type ]);
            }
            unset($old_due[$id]);
        }

        $concept->related()->detach(array_keys($old_due));
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

        $new_due = [];

        foreach ($lines as $line) {
            $title = $line[3];

            $is_done = false;
            if ($line[2] != ' ') {
                $is_done = true;
            }

            /*
             * Extract a due date
             */
            $due_at = null;
            if (preg_match('/\d{4}-\d{2}-\d{2}/', $title, $match)) {
                $due_at = $match[0];
                $title = preg_replace('/\s*\d{4}-\d{2}-\d{2}/', '', $title, 1);

                $due_concept = Concept::journal($due_at);
                $new_due[$due_concept->id] = true;
            }

            /*
             * Match and remove tags
             */
            preg_match_all('/#([[:alpha:]][\w-]*)/ui', $title, $tag_matches, PREG_PATTERN_ORDER);
            $title = trim(preg_replace('/\s*#\S+/', '', $title));

            /*
             * Extract persons
             */
            preg_match_all('/@(\w+)/u', $title, $person_matches, PREG_PATTERN_ORDER);
            $title = trim(preg_replace('/\s*@(\S+)/', '', $title));

            $persons = Concept::where('type', 'entangle:person')
                    ->whereIn('slug', $person_matches[1])
                    ->pluck('id');

            /*
             * Parse Markdown
             */
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

            $item->persons()->sync($persons);
        }

        Item::destroy(array_keys($items));

        $this->syncRelated($concept,'due', array_keys($new_due));
    }

    private function extractLinks($concept)
    {
        if (!preg_match_all('/\[([^\]]+)\]\(([^\)]+)\)/m',
            $concept->body, $links, PREG_SET_ORDER)) {

            return;
        }

        $new_link = [];

        foreach ($links as $link) {
            $url = $link[2];

            if (preg_match('#^(/concept)?/(\d+)$#', $url, $matches)) {
                $new_link[$matches[2]] = true;
            }
        }
        $this->syncRelated($concept,'link', array_keys($new_link));
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
        $this->extractLinks($concept);
    }
}