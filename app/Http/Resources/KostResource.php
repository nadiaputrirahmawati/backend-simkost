<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KostResource extends JsonResource
{
    /**
     * @mixin \App\Models\Kost
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'owner_id'        => $this->owner_id,
            'name'            => $this->name,
            'slug'            => $this->slug,
            'type'            => $this->type,
            'address'         => $this->address,
            'city'            => $this->city,
            'latitude'        => $this->latitude,
            'longitude'       => $this->longitude,
            'description'     => $this->description,
            'public_facility' => $this->public_facility,
            'regulation'      => $this->regulation,
            'rooms_count'     => $this->whenCounted('rooms'),
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
