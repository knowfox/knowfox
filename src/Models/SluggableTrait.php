<?php

namespace Knowfox\Models;

use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

trait SluggableTrait
{
    private static function makeSlug($model, $attempt) {
        if ($attempt > 0) {
            if (preg_match('/^(.*-)(\d+)$/', $model->slug, $matches)) {
                return $matches[1] . $attempt;
            }
            else {
                return $model->slug . '-' . $attempt;
            }
        }
        else {
            $slug_field = $model->slugField;
            return Str::slug($model->{$slug_field});
        }
    }

    public static function bootSluggableTrait()
    {
        static::updating(function ($model) {
            if (empty($model->slug)) {
                return;
            }
            $attempt = 1; // start at one. We already have an initial slug
            do {
                if (0 == $model->where('slug', $model->slug)
                    ->where('parent_id', $model->parent_id)
                    ->where('id', '!=', $model->id)
                    ->count()) {
                    break;
                }
                $model->slug = self::makeSlug($model, $attempt);
                $attempt++;
            }
            while (true);
        });

        static::created(function ($model) {
            if (empty($model->slug)) {
                $attempt = 0;
                $success = false;
                do {
                    $model->slug = self::makeSlug($model, $attempt);
                    $attempt++;

                    try {
                        $success = $model->save();
                    }
                    catch (QueryException $e) {
                        if ($e->getCode() != 23000) {
                            throw $e;
                        }
                    }
                }
                while (!$success);
            }
        });
    }

}
