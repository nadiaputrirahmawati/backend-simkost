<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\ListOwnerComplaintsRequest;
use App\Http\Requests\Owner\UpdateComplaintResponseRequest;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerComplaintController extends Controller
{
    use ApiResponse;

    /**
     * List complaints for rooms in kosts owned by the authenticated owner.
     * @tags Owner Complaint
     */
    public function index(ListOwnerComplaintsRequest $request): JsonResponse
    {
        $owner = $request->user();

        $query = Complaint::whereHas('room.kost', fn ($q) => $q->where('owner_id', $owner->id))
            ->with(['user', 'room.kost']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $complaints = $query->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        return $this->paginateResponse(
            ComplaintResource::collection($complaints),
            'Daftar pengaduan berhasil diambil.',
        );
    }

    /**
     * Detail of a specific complaint owned by the owner.
     * @tags Owner Complaint
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $complaint = $this->findOwnedComplaint($request, $id);

        if (! $complaint) {
            return $this->errorResponse('Pengaduan tidak ditemukan.', 404);
        }

        $complaint->load(['user', 'room.kost']);

        return $this->successResponse(
            'Detail pengaduan berhasil diambil.',
            new ComplaintResource($complaint),
        );
    }

    /**
     * Update complaint status and response feedback.
     * @tags Owner Complaint
     */
    public function update(UpdateComplaintResponseRequest $request, string $id): JsonResponse
    {
        $complaint = $this->findOwnedComplaint($request, $id);

        if (! $complaint) {
            return $this->errorResponse('Pengaduan tidak ditemukan.', 404);
        }

        $complaint->update([
            'status' => $request->input('status'),
            'complaint_feedback' => $request->input('complaint_feedback'),
        ]);

        $complaint->load(['user', 'room.kost']);

        return $this->successResponse(
            'Tanggapan pengaduan berhasil diperbarui.',
            new ComplaintResource($complaint),
        );
    }

    private function findOwnedComplaint(Request $request, string $id): ?Complaint
    {
        return Complaint::where('id', $id)
            ->whereHas('room.kost', fn ($q) => $q->where('owner_id', $request->user()->id))
            ->first();
    }
}
