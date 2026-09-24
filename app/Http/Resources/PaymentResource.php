<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'contract_id' => $this->contract_id,
            'order_id' => $this->order_id,
            'amount' => $this->amount,
            'status' => $this->status,
            'payment_type' => $this->payment_type,
            'payment_date' => $this->payment_date,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'contract' => $this->whenLoaded('contract', fn () => [
                'id' => $this->contract->id,
                'status' => $this->contract->status,
                'monthly_price' => $this->contract->monthly_price,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
