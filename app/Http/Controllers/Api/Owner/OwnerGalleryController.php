<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreGalleryRequest;
use App\Http\Requests\Owner\UpdateGalleryRequest;
use App\Http\Resources\GalleryResource;
use App\Models\Gallery;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OwnerGalleryController extends Controller
{
    use ApiResponse;

    /**
     * List galleries for a kost (optionally filtered by room).
     * @tags Owner Gallery
     */
    public function index(Request $request, string $kostId): JsonResponse
    {
        $kost = $request->user()->kosts()->find($kostId);

        if (! $kost) {
            return $this->errorResponse('Kost tidak ditemukan.', 404);
        }

        $query = $kost->galleries();

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->input('room_id'));
        }

        $galleries = $query->orderByDesc('is_primary')->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        return $this->paginateResponse(
            GalleryResource::collection($galleries),
            'Galeri berhasil diambil.',
        );
    }

    /**
     * Upload and store a gallery image.
     * @tags Owner Gallery
     */
    public function store(StoreGalleryRequest $request, string $kostId): JsonResponse
    {
        $kost = $request->user()->kosts()->find($kostId);

        if (! $kost) {
            return $this->errorResponse('Kost tidak ditemukan.', 404);
        }

        // If room_id provided, verify the room belongs to this kost
        if ($request->filled('room_id')) {
            if (! $kost->rooms()->where('id', $request->input('room_id'))->exists()) {
                return $this->errorResponse('Kamar tidak ditemukan di kost ini.', 404);
            }
        }

        $path = $request->file('image')->store('galleries', 'public');

        $gallery = $kost->galleries()->create([
            'room_id'    => $request->input('room_id'),
            'image_url'  => $path,
            'is_primary' => $request->boolean('is_primary', false),
        ]);

        // If marking as primary, unset other primary images for the same scope
        if ($gallery->is_primary) {
            $this->clearOtherPrimaries($gallery);
        }

        return $this->successResponse(
            'Foto berhasil diupload.',
            new GalleryResource($gallery),
            201,
        );
    }

    /**
     * Update gallery metadata (replace image or toggle is_primary).
     * @tags Owner Gallery
     */
    public function update(UpdateGalleryRequest $request, string $kostId, string $galleryId): JsonResponse
    {
        $gallery = $this->findOwnedGallery($request, $kostId, $galleryId);

        if (! $gallery) {
            return $this->errorResponse('Foto tidak ditemukan.', 404);
        }

        if ($request->hasFile('image')) {
            // Delete old file
            Storage::disk('public')->delete($gallery->image_url);
            $gallery->image_url = $request->file('image')->store('galleries', 'public');
        }

        if ($request->has('is_primary')) {
            $gallery->is_primary = $request->boolean('is_primary');
        }

        $gallery->save();

        if ($gallery->is_primary) {
            $this->clearOtherPrimaries($gallery);
        }

        return $this->successResponse(
            'Foto berhasil diperbarui.',
            new GalleryResource($gallery->fresh()),
        );
    }

    /**
     * Delete a gallery image.
     * @tags Owner Gallery
     */
    public function destroy(Request $request, string $kostId, string $galleryId): JsonResponse
    {
        $gallery = $this->findOwnedGallery($request, $kostId, $galleryId);

        if (! $gallery) {
            return $this->errorResponse('Foto tidak ditemukan.', 404);
        }

        Storage::disk('public')->delete($gallery->image_url);
        $gallery->delete();

        return $this->successResponse('Foto berhasil dihapus.');
    }

    /**
     * Find a gallery that belongs to a kost owned by the authenticated owner.
     */
    private function findOwnedGallery(Request $request, string $kostId, string $galleryId): ?Gallery
    {
        $kost = $request->user()->kosts()->find($kostId);

        if (! $kost) {
            return null;
        }

        return $kost->galleries()->find($galleryId);
    }

    /**
     * Unset other primary flags in the same scope (kost-level or room-level).
     */
    private function clearOtherPrimaries(Gallery $gallery): void
    {
        $query = Gallery::where('id', '!=', $gallery->id)
            ->where('is_primary', true);

        if ($gallery->room_id) {
            $query->where('kost_id', $gallery->kost_id)
                  ->where('room_id', $gallery->room_id);
        } else {
            $query->where('kost_id', $gallery->kost_id)
                  ->whereNull('room_id');
        }

        $query->update(['is_primary' => false]);
    }
}
