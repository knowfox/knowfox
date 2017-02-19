<?php

namespace Knowfox\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Relationship extends Pivot
{
    public static $types = [
        'uses' => ['nutzt', 'wird genutzt von'],
    ];

    public function getTypeAttribute($value)
    {
        $result = [ 'type' => $value ];

        if (!empty(self::$types[$value])) {
            return $result + [
                'labels' => self::$types[$value],
            ];
        }
        else {
            return $result;
        }
    }
}
