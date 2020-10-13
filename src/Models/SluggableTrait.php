<?php

namespace Knowfox\Models;

use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

trait SluggableTrait
{
    public static function bootSluggableTrait()
    {
        static::created(function ($model) {
            if (empty($model->slug)) {
                $slug_field = $model->slugField;

                $model->slug = Str::slug($model->{$slug_field});

                $attempt = 0;
                $success = false;
                do {
                    if ($attempt > 0) {
                        if (preg_match('/^(.*_)(\d+)$/', $model->slug, $matches)) {
                            $model->slug = $matches[1] . $attempt;
                        }
                        else {
                            $model->slug .= '-' . $attempt;
                        }
                    }
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
