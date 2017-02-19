<?php

namespace Knowfox\Models;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;
use cebe\markdown\GithubMarkdown;

class Concept extends Model
{
    use NodeTrait;

    public function getBodyAttribute($value)
    {
        $parser = new GithubMarkdown();
        $parser->html5 = true;
        return $parser->parse($value);
    }
}
