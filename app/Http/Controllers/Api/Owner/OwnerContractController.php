<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\ListOwnerContractsRequest;
use App\Http\Requests\Owner\VerifyContractRequest;
use App\Http\Resources\ContractResource;
use App\Models\Contract;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OwnerContractController extends Controller
{
    use ApiResponse;

    /**
     * List contracts across all kosts owned by the authenticated owner.
     * @tags Owner Contract
     */
    public function index(ListOwnerContractsRequest $request): JsonResponse
    {
        $owner = $request->user();

        $query = Contract::whereHas('room.kost', fn ($q) => $q->where('owner_id', $owner->id))
            ->with(['user', 'room.kost']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $contracts = $query->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        return $this->paginateResponse(
            ContractResource::collection($contracts),
            'Daftar kontrak berhasil diambil.',
        );
    }

    /**
     * Show contract detail with user profile, room, and payment history.
     * @tags Owner Contract
     */
    public function show(Request $request, string $contractId): JsonResponse
    {
        $contract = $this->findOwnedContract($request, $contractId);

        if (! $contract) {
            return $this->errorResponse('Kontrak tidak ditemukan.', 404);
        }

        $contract->load([
            'user',
            'room.kost',
            'payments' => fn ($q) => $q->orderByDesc('created_at'),
        ]);

        return $this->successResponse(
            'Detail kontrak berhasil diambil.',
            new ContractResource($contract),
        );
    }

    /**
     * Approve or reject a pending contract. Updates room availability inside a transaction.
     * @tags Owner Contract
     */
    public function verify(VerifyContractRequest $request, string $contractId, string $action): JsonResponse
    {
        $contract = $this->findOwnedContract($request, $contractId);

        if (! $contract) {
            return $this->errorResponse('Kontrak tidak ditemukan.', 404);
        }

        if ($contract->verification_contract !== 'pending') {
            return $this->errorResponse('Kontrak sudah diverifikasi sebelumnya.', 422);
        }

        DB::transaction(function () use ($contract, $action, $request) {
            if ($action === 'approve') {
                $contract->update([
                    'verification_contract' => 'completed',
                    'status' => 'active',
                ]);

                $contract->room->update(['status' => 'occupied']);
            } else {
                $contract->update([
                    'verification_contract' => 'rejected',
                    'rejection_feedback' => $request->input('rejection_feedback'),
                    'status' => 'cancelled',
                ]);

                // Room stays available — no occupied contract exists.
                // Only set to available if it wasn't already (defensive).
                if ($contract->room->status !== 'available') {
                    $contract->room->update(['status' => 'available']);
                }
            }
        });

        $message = $action === 'approve'
            ? 'Kontrak berhasil disetujui.'
            : 'Kontrak berhasil ditolak.';

        return $this->successResponse(
            $message,
            new ContractResource($contract->fresh()->load(['user', 'room.kost'])),
        );
    }

    /**
     * Find a contract that belongs to a kost owned by the authenticated owner.
     */
    private function findOwnedContract(Request $request, string $contractId): ?Contract
    {
        return Contract::where('id', $contractId)
            ->whereHas('room.kost', fn ($q) => $q->where('owner_id', $request->user()->id))
            ->first();
    }
}
