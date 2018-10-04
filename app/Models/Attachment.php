<?php

namespace Knowfox\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $casts = [
        'is_default' => 'boolean',
        'data' => 'array',
    ];

    protected $fillable = ['original_id', 'concept_id', 'type', 'name', 'is_default'];

    public function concept()
    {
        return $this->belongsTo(Concept::class);
    }

    public function original()
    {
        return $this->belongsTo(Attachment::class,'original_id');
    }
}
