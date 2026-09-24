<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KycResource extends JsonResource
{
    /**
     * @mixin \App\Models\User
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'no_ktp' => $this->no_ktp,
            'npwp' => $this->npwp,
            'ktp_picture' => $this->ktp_picture,
            'ktp_picture_person' => $this->ktp_picture_person,
            'status_verification' => $this->status_verification,
            'rejection_feedback' => $this->rejection_feedback,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
