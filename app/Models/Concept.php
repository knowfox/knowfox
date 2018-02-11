<?php

namespace Knowfox\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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


class MyGithubMarkdown extends GithubMarkdown
{
    protected function renderTable($block)
    {
        $table = parent::renderTable($block);
        return preg_replace('#<table>#', '<table class="table">', $table);
    }
}

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

    protected $dispatchesEvents = [
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

    private function textEnd($html, $offs)
    {
        return strpos(substr($html, $offs), '<');
    }

    private function tagEnd($html, $offs)
    {
        $pos = strpos(substr($html, $offs), '>');
    }

    private function inALink($html, $offs)
    {
        return preg_match('#<(.{2})#', substr($html, $offs), $match)
            && $match[1] == '/a';
    }

    private function replaceDates($html)
    {
        $segments = [];
        $last = 0;
        preg_match_all('/\d{4}-\d{2}-\d{2}/', $html, $matches, PREG_PATTERN_ORDER|PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as $match) {
            if (preg_match('/\S/', substr($html, $match[1] - 1, 1))) {
                // Not prefixed by whitespace
                continue;
            }

            if ($this->inALink($html, $match[1])) {
                continue;
            }
            $segments[] = $segment = substr($html, $last, $match[1] - $last);

            $date = $match[0];
            $last += strlen($segment) + strlen($date);

            $segments[] = '<a href="/' . $date . '">' . $date . '</a>';
        }
        $segments[] = substr($html, $last);

        return join('', $segments);
    }

    private function replaceTags($html)
    {
        $segments = [];
        $last = 0;
        preg_match_all('/#([[:alpha:]][\w-]*)/ui', $html, $matches, PREG_SET_ORDER|PREG_OFFSET_CAPTURE);
        foreach ($matches as $match) {
            if ($this->inALink($html, $match[0][1])) {
                continue;
            }
            $segments[] = $segment = substr($html, $last, $match[0][1] - $last);

            $tag = $match[1][0];
            $last += strlen($segment) + strlen($match[0][0]);

            $segments[] = '<a class="label label-default" href="/concepts?tag=' . Str::slug($tag) . '">' . ucfirst($tag) . '</a>';
        }
        $segments[] = substr($html, $last);

        return join('', $segments);
    }

    private function replacePersons($html)
    {
        $segments = [];
        $last = 0;
        preg_match_all('/@(\w+)/u', $html, $matches, PREG_SET_ORDER|PREG_OFFSET_CAPTURE);
        foreach ($matches as $match) {
            if ($this->inALink($html, $match[0][1])) {
                continue;
            }
            $segments[] = $segment = substr($html, $last, $match[0][1] - $last);

            $person = $match[1][0];
            $last += strlen($segment) + strlen($match[0][0]);

            $segments[] = '<a class="label label-info" href="/person/' . $person . '">' . ucfirst($person) . '</a>';
        }
        $segments[] = substr($html, $last);

        return join('', $segments);
    }

    public function getRenderedBodyAttribute($value)
    {
        $parser = new MyGithubMarkdown();
        $parser->html5 = true;

        $html = $parser->parse($this->body);

        $html = $this->replaceDates($html);

        $html = $this->replaceTags($html);

        $html = $this->replacePersons($html);

        return '<div class="body" data-uuid="' . $this->uuid . '">' . $html . '</div>';
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

    /**
     * @param $query
     * @param null $letter
     * @return mixed
     */
    public function getPaginated($query, $sort = null, $letter = null, $paginate_options = [])
    {
        if ($sort) {
            if ($sort == 'alpha') {
                if ($letter) {
                    $letter = ucfirst(substr($letter, 0, 1));
                    if ($letter < 'A' || $letter > 'Z') {
                        $query->where('title', '<', 'A');
                    }
                    else {
                        $query
                            ->where('title', '>=', $letter)
                            ->where('title', '<', chr(ord($letter) + 1));
                    }
                }
                $query->orderBy('title', 'asc');
                $paginator = call_user_func_array([$query, 'paginate'], $paginate_options);

                if ($letter) {
                    $paginator->appends('letter', $letter);
                }
                return $paginator;
            }
            else
            if ($sort == 'created') {
                $query->orderBy('created_at', 'desc');
                return call_user_func_array([$query, 'paginate'], $paginate_options);
            }
        }
        $query->defaultOrder();
        return call_user_func_array([$query, 'paginate'], $paginate_options);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public static function journal($date_string = null)
    {
        /** @var \Carbon\Carbon $date */
        if ($date_string) {
            $date = Carbon::createFromFormat('Y-m-d', $date_string);
            if (!$date) {
                throw \Exception("{$date_string} is not a date");
            }
        }
        else {
            $date = Carbon::today();
        }

        $root = self::whereIsRoot()->where('title', 'Journal')->first();
        if (!$root) {
            throw \Exception('No "Journal" root');
        }

        $year = self::firstOrCreate([
            'parent_id' => $root->id,
            'title' => $date->format('Y'),
            'owner_id' => Auth::id(),
        ]);

        $month = self::firstOrCreate([
            'parent_id' => $year->id,
            'title' => $date->format('m'),
            'owner_id' => Auth::id(),
        ]);

        $concept = self::where('parent_id', $month->id)
            ->where('title', 'like', $date->format('Y-m-d') . '%')
            ->first();
        if (!$concept) {
            $nav = '';

            if ($date->dayOfWeek == Carbon::MONDAY) {
                $friday = (clone $date)->subDay(3)->format('Y-m-d');
                $nav .= "[friday ({$friday})](/{$friday}) | ";
            }

            $prev = (clone $date)->subDay(1)->format('Y-m-d');
            $next = (clone $date)->addDay(1)->format('Y-m-d');
            $nav .= "[yesterday ({$prev})](/{$prev}) | [tomorrow ({$next})](/{$next})";

            if ($date->dayOfWeek == Carbon::FRIDAY) {
                $monday = (clone $date)->addDay(3)->format('Y-m-d');
                $nav .= "| [monday ({$monday})](/{$monday})";
            }

            $concept = self::create([
                'parent_id' => $month->id,
                'title' => $date->format('Y-m-d l:') . ' Journal',
                'owner_id' => Auth::id(),
                'body' => $nav . "\n\n",
            ]);
            $concept->tag('Journal');
        }

        return $concept;
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class)->orderBy('is_default', 'desc');
    }
}
