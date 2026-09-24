<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\OwnerUpdateProfileRequest;
use App\Http\Resources\OwnerResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerProfileController extends Controller
{
    use ApiResponse;

    /**
     * Get owner profile.
     * @tags Owner Profile
     */
    public function show(Request $request): JsonResponse
    {
        return $this->successResponse(
            'Profil berhasil diambil.',
            new OwnerResource($request->user()),
        );
    }

    /**
     * Update owner profile.
     * @tags Owner Profile
     */
    public function update(OwnerUpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return $this->successResponse(
            'Profil berhasil diperbarui.',
            new OwnerResource($user->fresh()),
        );
    }
}
