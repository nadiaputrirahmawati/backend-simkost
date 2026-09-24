<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreRoomRequest;
use App\Http\Requests\Owner\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\Room;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerRoomController extends Controller
{
    use ApiResponse;

    /**
     * List rooms for a specific kost owned by the authenticated owner.
     * @tags Owner Room
     */
    public function index(Request $request, string $kostId): JsonResponse
    {
        $kost = $request->user()->kosts()->find($kostId);

        if (! $kost) {
            return $this->errorResponse('Kost tidak ditemukan.', 404);
        }

        $rooms = $kost->rooms()
            ->with('galleries')
            ->orderBy('room_number')
            ->paginate($request->input('per_page', 15));

        return $this->paginateResponse(
            RoomResource::collection($rooms),
            'Daftar kamar berhasil diambil.',
        );
    }

    /**
     * Store a new room in a kost owned by the authenticated owner.
     * @tags Owner Room
     */
    public function store(StoreRoomRequest $request, string $kostId): JsonResponse
    {
        $kost = $request->user()->kosts()->find($kostId);

        if (! $kost) {
            return $this->errorResponse('Kost tidak ditemukan.', 404);
        }

        $room = $kost->rooms()->create($request->validated());

        return $this->successResponse(
            'Kamar berhasil dibuat.',
            new RoomResource($room->load('galleries')),
            201,
        );
    }

    /**
     * Show a specific room.
     * @tags Owner Room
     */
    public function show(Request $request, string $kostId, string $roomId): JsonResponse
    {
        $room = $this->findOwnedRoom($request, $kostId, $roomId);

        if (! $room) {
            return $this->errorResponse('Kamar tidak ditemukan.', 404);
        }

        return $this->successResponse(
            'Detail kamar berhasil diambil.',
            new RoomResource($room->load('galleries')),
        );
    }

    /**
     * Update a room.
     * @tags Owner Room
     */
    public function update(UpdateRoomRequest $request, string $kostId, string $roomId): JsonResponse
    {
        $room = $this->findOwnedRoom($request, $kostId, $roomId);

        if (! $room) {
            return $this->errorResponse('Kamar tidak ditemukan.', 404);
        }

        $room->update($request->validated());

        return $this->successResponse(
            'Kamar berhasil diperbarui.',
            new RoomResource($room->fresh()->load('galleries')),
        );
    }

    /**
     * Delete a room.
     * @tags Owner Room
     */
    public function destroy(Request $request, string $kostId, string $roomId): JsonResponse
    {
        $room = $this->findOwnedRoom($request, $kostId, $roomId);

        if (! $room) {
            return $this->errorResponse('Kamar tidak ditemukan.', 404);
        }

        $room->delete();

        return $this->successResponse('Kamar berhasil dihapus.');
    }

    /**
     * Find a room that belongs to a kost owned by the authenticated owner.
     */
    private function findOwnedRoom(Request $request, string $kostId, string $roomId): ?Room
    {
        $kost = $request->user()->kosts()->find($kostId);

        if (! $kost) {
            return null;
        }

        return $kost->rooms()->with('galleries')->find($roomId);
    }
}
