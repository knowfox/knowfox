<?php

namespace Knowfox\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Relationship extends Pivot
{
    protected $table = 'relationships';

    public function forwardLabel()
    {
        $config = config('knowfox');

        return !empty($config['relationships'][$this->type][0])
            ? $config['relationships'][$this->type][0]
            : $this->type;
    }

    public function reverseLabel()
    {
        $config = config('knowfox');

        return !empty($config['relationships'][$this->type][1])
            ? $config['relationships'][$this->type][1]
            : $this->type;
    }
}
