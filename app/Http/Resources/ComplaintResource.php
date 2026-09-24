<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'room_id' => $this->room_id,
            'description' => $this->description,
            'complaint_feedback' => $this->complaint_feedback,
            'status' => $this->status,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'phone_number' => $this->user->phone_number,
            ]),
            'room' => $this->whenLoaded('room', fn () => [
                'id' => $this->room->id,
                'room_number' => $this->room->room_number,
                'room_size' => $this->room->room_size,
                'price' => $this->room->price,
                'status' => $this->room->status,
                'kost' => $this->whenLoaded('room.kost', fn () => [
                    'id' => $this->room->kost->id,
                    'name' => $this->room->kost->name,
                    'address' => $this->room->kost->address,
                ]),
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
