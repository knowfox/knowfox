<?php

namespace Knowfox\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Attachment extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'original_id' => $this->original_id,
            'concept_id' => $this->concept_id,
            'is_default' => $this->is_default,
            'type' => $this->type,
            'data' => $this->data,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
