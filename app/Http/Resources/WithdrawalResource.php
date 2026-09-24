<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'amount' => $this->amount,
            'target_bank' => $this->target_bank,
            'target_account_number' => $this->target_account_number,
            'target_account_holder' => $this->target_account_holder,
            'proof' => $this->proof,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'owner' => [
                'id' => $this->whenLoaded('owner', fn () => $this->owner->id),
                'name' => $this->whenLoaded('owner', fn () => $this->owner->name),
                'email' => $this->whenLoaded('owner', fn () => $this->owner->email),
                'bank_name' => $this->whenLoaded('owner', fn () => $this->owner->bank_name),
                'bank_account_number' => $this->whenLoaded('owner', fn () => $this->owner->bank_account_number),
                'bank_account_holder' => $this->whenLoaded('owner', fn () => $this->owner->bank_account_holder),
                'balance' => $this->whenLoaded('owner', fn () => $this->owner->balance),
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
