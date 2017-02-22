<?php

namespace Knowfox\Models;

use Ramsey\Uuid\Uuid;

trait UuidTrait
{
    public static function bootUuidTrait()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Uuid::uuid1()->toString();
            }
        });

        static::updating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Uuid::uuid1()->toString();
            }
        });
    }

}
