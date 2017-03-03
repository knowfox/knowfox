<?php

namespace Knowfox\Models;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;
use cebe\markdown\GithubMarkdown;
use Conner\Tagging\Taggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Knowfox\User;
use Symfony\Component\Yaml\Yaml;

class Concept extends Model {
    use SoftDeletes;
    use NodeTrait;
    use Taggable;
    use UuidTrait;
    use SluggableTrait;

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts = [
        'is_flagged' => 'boolean',
    ];

    protected $slugField = 'title';
    protected $fillable = ['title', 'summary', 'body', 'parent_id', 'source_url', 'todoist_id', 'slug', 'is_flagged', 'status', 'language', 'uuid', 'relations', 'owner_id'];

    public function getRelationsAttribute()
    {
        $result = [];
        foreach ($this->related as $related) {
            // @todo No more than one relation between two concepts
            $result[$related->id] = [ 'type' => $related->pivot->type ];
        }
        return Yaml::dump($result, 1);
    }

    public function setRelationsAttribute($value)
    {
        $this->related()->sync(Yaml::parse($value));
    }

    public function getRenderedBodyAttribute($value)
    {
        $parser = new GithubMarkdown();
        $parser->html5 = TRUE;
        return $parser->parse($this->body);
    }

    public function related()
    {
        return $this->belongsToMany(Concept::class, 'relationships', 'source_id', 'target_id')
            ->withPivot('type')
            ->using(Relationship::class)
            ->withTimestamps();

    }

    public function inverseRelated()
    {
        return $this->belongsToMany(Concept::class, 'relationships', 'target_id', 'source_id')
            ->withPivot('type')
            ->using(Relationship::class)
            ->withTimestamps();

    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function shares()
    {
        return $this->belongsToMany(User::class, 'shares', 'concept_id', 'user_id')
            ->withPivot('permissions')
            ->using(Share::class)
            ->withTimestamps();
    }

}
