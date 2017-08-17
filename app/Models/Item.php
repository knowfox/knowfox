<?php

namespace Knowfox\Models;

use Conner\Tagging\Taggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use Taggable, SoftDeletes;

    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'due_at', 'done_at'];
    protected $casts = [
        'is_done' => 'boolean',
    ];

    protected $fillable = ['title', 'owner_id', 'concept_id', 'is_done', 'due_at', 'done_at'];

    public function concept()
    {
        return $this->belongsTo(Concept::class);
    }
}
