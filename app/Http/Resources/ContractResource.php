<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PaymentResource;

class ContractResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'user_id' => $this->user_id,
            'room_id' => $this->room_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'monthly_price' => $this->monthly_price,
            'deposit_amount' => $this->deposit_amount,
            'status' => $this->status,
            'contract_type' => $this->contract_type,
            'verification_contract' => $this->verification_contract,
            'rejection_feedback' => $this->rejection_feedback,
            'owner' => $this->whenLoaded('owner', fn () => [
                'id' => $this->owner->id,
                'name' => $this->owner->name,
                'email' => $this->owner->email,
            ]),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
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
            'payments' => $this->whenLoaded('payments', fn () => PaymentResource::collection($this->payments)),
            'payments_count' => $this->whenCounted('payments'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
