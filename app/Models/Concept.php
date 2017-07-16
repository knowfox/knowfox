<?php

namespace Knowfox\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Kalnoy\Nestedset\NodeTrait;
use cebe\markdown\GithubMarkdown;
use Conner\Tagging\Taggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Knowfox\Observers\ConceptObserver;
use Knowfox\User;
use Symfony\Component\Yaml\Yaml;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Mpociot\Versionable\VersionableTrait;

class Concept extends Model {
    use SoftDeletes;
    use NodeTrait {
        children as nodeChildren;
    }
    use Taggable;
    use UuidTrait;
    use SluggableTrait;
    use VersionableTrait;

    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'viewed_at'];
    protected $casts = [
        'is_flagged' => 'boolean',
    ];

    protected $slugField = 'title';
    protected $fillable = ['type', 'title', 'summary', 'body', 'parent_id', 'source_url', 'todoist_id', 'slug', 'is_flagged', 'status', 'language', 'uuid', 'relations', 'data', 'owner_id'];

    protected $events = [
        'saving' => ConceptObserver::class,
    ];

    protected $dontVersionFields = [ 'viewed_at', 'viewed_count' ];

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

    private function renderValue($value)
    {
        $result = '';
        if (is_array($value) || is_object($value)) {
            $result .= $this->renderData($value);
        }
        else
        if (is_bool($value)) {
            $result .= $value ? 'true' : 'false';
        }
        else {
            $result .= $value;
        }
        return $result;
    }

    private function renderData($data)
    {
        $result = '';
        $tag = null;
        foreach ($data as $key => $value) {
            if (!$tag) {
                if (is_numeric($key)) {
                    $tag = 'ul';
                }
                else {
                    $tag = 'dl';
                }
                $result .= "<{$tag}>";
            }
            if (is_numeric($key)) {
                $result .= '<li>' . $this->renderValue($value) . '</li>';
            }
            else {
                $result .= '<dt>' . $key . '</dt><dd>' . $this->renderValue($value) . '</dd>';
            }
        }
        return $result . "</{$tag}>";
    }

    public function getRenderedConfigAttribute($value)
    {
        $data = (object)Yaml::parse($this->data);
        return $this->renderData($data);
    }

    public function renderedDiff($version)
    {
        return $this->renderData($version->diff());
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

    public function children()
    {
        return $this->nodeChildren()
            ->where('owner_id', Auth::id());
    }

    public function newFromBuilder($attributes = [], $connection = NULL)
    {
        if (!empty($attributes->type)) {
            $scoped_type = preg_split('/:\s*/', $attributes->type, 2);
            if (count($scoped_type) > 1) {
                $package = $scoped_type[0];
                $type = $scoped_type[1];

                $class_name = "\\Knowfox\\" . ucfirst($package) . "\\Models\\" . ucfirst($type);
                if (class_exists($class_name)) {
                    $instance = (new $class_name)->newInstance([], true);

                    $instance->setRawAttributes((array) $attributes, true);
                    $instance->setConnection($connection ?: $this->getConnectionName());

                    return $instance;
                }
            }
        }
        return parent::newFromBuilder($attributes, $connection);
    }

    public function getPaginatedChildren($letter = null)
    {
        if (!empty($this->config->sort) && $this->config->sort == 'alpha') {
            $children = $this->children();
            if ($letter) {
                $letter = ucfirst(substr($letter, 0, 1));
                if ($letter < 'A' || $letter > 'Z') {
                    $children->where('title', '<', 'A');
                }
                else {
                    $children
                        ->where('title', '>=', $letter)
                        ->where('title', '<', chr(ord($letter) + 1));
                }
            }
            $children = $children
                ->orderBy('title', 'asc')
                ->paginate();

            if ($letter) {
                $children->appends('letter', $letter);
            }
            return $children;
        }
        else {
            return $this->children()->defaultOrder()->paginate();
        }
    }
}
