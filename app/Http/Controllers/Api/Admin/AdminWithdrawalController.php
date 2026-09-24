<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveWithdrawalRequest;
use App\Http\Requests\Admin\ListWithdrawalsRequest;
use App\Http\Requests\Admin\RejectWithdrawalRequest;
use App\Http\Resources\WithdrawalResource;
use App\Models\User;
use App\Models\Withdrawal;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminWithdrawalController extends Controller
{
    use ApiResponse;

    /**
     * Daftar permohonan penarikan dana dengan filter status & pagination.
     */
    /**
     * List withdrawal requests with optional status filter.
     * @tags Withdrawal Management
     */
    public function index(ListWithdrawalsRequest $request): JsonResponse
    {
        $query = Withdrawal::with('owner');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $withdrawals = $query->latest()->paginate($request->input('per_page', 15));

        return $this->paginateResponse(
            WithdrawalResource::collection($withdrawals),
            'Daftar permohonan pencairan dana berhasil diambil.',
        );
    }

    /**
     * Detail permohonan penarikan dana beserta snapshot rekening tujuan.
     */
    /**
     * Get withdrawal request detail with owner snapshot.
     * @tags Withdrawal Management
     */
    public function show(string $id): JsonResponse
    {
        $withdrawal = Withdrawal::with('owner')->find($id);

        if (! $withdrawal) {
            return $this->errorResponse('Permohonan pencairan dana tidak ditemukan.', JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->successResponse(
            'Detail permohonan pencairan dana berhasil diambil.',
            new WithdrawalResource($withdrawal),
        );
    }

    /**
     * Setujui permohonan pencairan → upload bukti transfer.
     */
    /**
     * Approve a pending withdrawal with proof of transfer upload.
     * @tags Withdrawal Management
     */
    public function approve(ApproveWithdrawalRequest $request, string $id): JsonResponse
    {
        $withdrawal = Withdrawal::with('owner')->find($id);

        if (! $withdrawal) {
            return $this->errorResponse('Permohonan pencairan dana tidak ditemukan.', JsonResponse::HTTP_NOT_FOUND);
        }

        if ($withdrawal->status !== 'pending') {
            return $this->errorResponse(
                'Hanya permohonan dengan status pending yang dapat disetujui.',
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $proofPath = $request->file('proof')->store('withdrawal-proofs', 'private');

        $withdrawal->update([
            'proof' => $proofPath,
            'status' => 'approved',
        ]);

        return $this->successResponse(
            'Permohonan pencairan dana berhasil disetujui.',
            new WithdrawalResource($withdrawal->fresh('owner')),
        );
    }

    /**
     * Tolak permohonan pencairan → kembalikan saldo ke owner (race-condition safe).
     *
     * ponytail: lockForUpdate + DB::transaction covers single-server. For
     * multi-server / queue-based processing, add a Redis distributed lock
     * keyed on owner_id before this block.
     */
    /**
     * Reject a pending withdrawal and refund owner balance (race-condition safe).
     * @tags Withdrawal Management
     */
    public function reject(RejectWithdrawalRequest $request, string $id): JsonResponse
    {
        $withdrawal = Withdrawal::find($id);

        if (! $withdrawal) {
            return $this->errorResponse('Permohonan pencairan dana tidak ditemukan.', JsonResponse::HTTP_NOT_FOUND);
        }

        if ($withdrawal->status !== 'pending') {
            return $this->errorResponse(
                'Hanya permohonan dengan status pending yang dapat ditolak.',
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $rejectionReason = $request->validated('rejection_reason');

        $withdrawal = DB::transaction(function () use ($withdrawal, $rejectionReason) {
            // Lock owner row to prevent concurrent balance manipulation
            $owner = User::where('id', $withdrawal->owner_id)->lockForUpdate()->first();

            $owner->increment('balance', $withdrawal->amount);

            $withdrawal->update([
                'status' => 'rejected',
                'rejection_reason' => $rejectionReason,
            ]);

            return $withdrawal;
        });

        return $this->successResponse(
            'Permohonan pencairan dana berhasil ditolak. Saldo telah dikembalikan ke pemilik.',
            new WithdrawalResource($withdrawal->fresh('owner')),
        );
    }
}
