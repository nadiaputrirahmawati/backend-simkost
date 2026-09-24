<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GalleryResource extends JsonResource
{
    /**
     * @mixin \App\Models\Gallery
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'kost_id'    => $this->kost_id,
            'room_id'    => $this->room_id,
            'image_url'  => $this->image_url,
            'is_primary' => $this->is_primary,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
