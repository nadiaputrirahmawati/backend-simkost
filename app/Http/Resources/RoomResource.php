<?php

namespace App\Http\Resources;

use App\Http\Resources\GalleryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    /**
     * @mixin \App\Models\Room
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'kost_id'        => $this->kost_id,
            'room_number'    => $this->room_number,
            'room_size'      => $this->room_size,
            'price'          => $this->price,
            'deposit_amount' => $this->deposit_amount,
            'status'         => $this->status,
            'room_facility'  => $this->room_facility,
            'galleries'      => GalleryResource::collection($this->whenLoaded('galleries')),
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
