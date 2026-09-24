<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreKostRequest;
use App\Http\Requests\Owner\UpdateKostRequest;
use App\Http\Resources\KostResource;
use App\Models\Kost;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerKostController extends Controller
{
    use ApiResponse;

    /**
     * List all kosts for the authenticated owner.
     * @tags Owner Kost
     */
    public function index(Request $request): JsonResponse
    {
        $kosts = $request->user()
            ->kosts()
            ->withCount('rooms')
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        return $this->paginateResponse(
            KostResource::collection($kosts),
            'Daftar kost berhasil diambil.',
        );
    }

    /**
     * Store a new kost.
     * @tags Owner Kost
     */
    public function store(StoreKostRequest $request): JsonResponse
    {
        $kost = $request->user()->kosts()->create($request->validated());
        $kost->loadCount('rooms');

        return $this->successResponse(
            'Kost berhasil dibuat.',
            new KostResource($kost),
            201,
        );
    }

    /**
     * Show a specific kost owned by the authenticated owner.
     * @tags Owner Kost
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $kost = $this->findOwnedKost($request, $id);

        if (! $kost) {
            return $this->errorResponse('Kost tidak ditemukan.', 404);
        }

        $kost->loadCount('rooms');

        return $this->successResponse(
            'Detail kost berhasil diambil.',
            new KostResource($kost),
        );
    }

    /**
     * Update a kost owned by the authenticated owner.
     * @tags Owner Kost
     */
    public function update(UpdateKostRequest $request, string $id): JsonResponse
    {
        $kost = $this->findOwnedKost($request, $id);

        if (! $kost) {
            return $this->errorResponse('Kost tidak ditemukan.', 404);
        }

        $kost->update($request->validated());
        $kost->loadCount('rooms');

        return $this->successResponse(
            'Kost berhasil diperbarui.',
            new KostResource($kost->fresh()),
        );
    }

    /**
     * Delete a kost owned by the authenticated owner.
     * @tags Owner Kost
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $kost = $this->findOwnedKost($request, $id);

        if (! $kost) {
            return $this->errorResponse('Kost tidak ditemukan.', 404);
        }

        $kost->delete();

        return $this->successResponse('Kost berhasil dihapus.', null, 200);
    }

    /**
     * Find a kost that belongs to the authenticated owner.
     */
    private function findOwnedKost(Request $request, string $id): ?Kost
    {
        return $request->user()
            ->kosts()
            ->withCount('rooms')
            ->find($id);
    }
}
