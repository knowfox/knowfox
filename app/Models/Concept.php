<?php

namespace Knowfox\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Kalnoy\Nestedset\NodeTrait;
use cebe\markdown\GithubMarkdown;
use Conner\Tagging\Taggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Knowfox\User;
use Symfony\Component\Yaml\Yaml;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Concept extends Model {
    use SoftDeletes;
    use NodeTrait;
    use Taggable;
    use UuidTrait;
    use SluggableTrait;

    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'viewed_at'];
    protected $casts = [
        'is_flagged' => 'boolean',
    ];

    protected $slugField = 'title';
    protected $fillable = ['type', 'title', 'summary', 'body', 'parent_id', 'source_url', 'todoist_id', 'slug', 'is_flagged', 'status', 'language', 'uuid', 'relations', 'data', 'owner_id'];

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
        $related = Yaml::parse($value);
        if (empty($related)) {
            $related = [];
        }
        $this->related()->sync($related);
    }

    public function getRenderedBodyAttribute($value)
    {
        $parser = new GithubMarkdown();
        $parser->html5 = TRUE;
        return $parser->parse($this->body);
    }

    public function getConfigAttribute($value)
    {
        return (object)Yaml::parse($this->data);
    }

    public function setConfigAttribute($value)
    {
        /*
         * Yaml::dump() does not convert objects. Therefore,
         * before converting to Yaml, recursively cast objects to arrays
         */
        $this->attributes['data'] = Yaml::dump(json_decode(json_encode($value), true));
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

    public function sameDay()
    {
        if (!preg_match('/(\d{4}-\d{2}-\d{2})\s/', $this->title, $matches)) {
            return null;
        }

        $date = Carbon::createFromFormat('Y-m-d', $matches[1]);
        $begins_at = $date->copy()->hour(0)->minute(0);
        $ends_at = $date->copy()->addDay()->hour(0)->minute(0);

        return self::where('owner_id', Auth::id())
            ->whereRaw("(created_at >= '{$begins_at}' AND created_at < '{$ends_at}' OR updated_at >= '{$begins_at}' AND updated_at < '{$ends_at}')")
            ->where('id', '!=', $this->id)
            ->where('title', '!=', $date->format('Y'))
            ->where('title', '!=', $date->format('m'))
            ->orderBy('created_at', 'asc')
            ->orderBy('updated_at', 'asc');
    }

    public function letters()
    {
        return $this->children()
            ->select(DB::raw('DISTINCT(SUBSTR(title, 1, 1)) AS t, COUNT(*) as n'))
            ->groupBy('t')
            ->orderBy('t', 'ASC');
    }
}
