<?php

namespace Knowfox\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Relationship extends Pivot
{
    public function getTypeAttribute($value)
    {
        $result = [ 'type' => $value ];
        $config = config('knowfox');

        if (!empty($config['relationships'][$value])) {
            return $result + [
                'labels' => $config['relationships'][$value],
            ];
        }
        else {
            return $result;
        }
    }
}
