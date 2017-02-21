<?php

namespace Knowfox\Models;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;
use cebe\markdown\GithubMarkdown;
use Conner\Tagging\Taggable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Concept extends Model {
    use SoftDeletes;
    use NodeTrait;
    use Taggable;
    use SluggableTrait;

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    protected $slugField = 'title';
    protected $fillable = ['title', 'summary', 'body', 'parent_id', 'source_url', 'todoist_id', 'slug', 'is_flagged', 'weight', 'status', 'language'];

    public function getRenderedBodyAttribute($value) {
        $parser = new GithubMarkdown();
        $parser->html5 = TRUE;
        return $parser->parse($this->body);
    }

    public function related() {
        return $this->belongsToMany(Concept::class, 'relationships', 'source_id', 'target_id')
            ->withPivot('type')
            ->using(Relationship::class);

    }

    public function inverseRelated() {
        return $this->belongsToMany(Concept::class, 'relationships', 'target_id', 'source_id')
            ->withPivot('type')
            ->using(Relationship::class);

    }
}
