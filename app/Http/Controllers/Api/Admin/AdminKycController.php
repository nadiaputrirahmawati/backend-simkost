<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectKycRequest;
use App\Http\Resources\KycResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminKycController extends Controller
{
    use ApiResponse;

    /**
     * Daftar antrean verifikasi KYC (status_verification = 'pending).
     */
    /**
     * List pending KYC verification queue.
     * @tags KYC Verification
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::where('status_verification', 'pending');

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('no_ktp', 'like', "%{$keyword}%")
                    ->orWhere('phone_number', 'like', "%{$keyword}%");
            });
        }

        $users = $query->latest()->paginate($request->input('per_page', 15));

        return $this->paginateResponse(KycResource::collection($users), 'Antrean verifikasi KYC berhasil diambil.');
    }

    /**
     * Detail dokumen KYC milik pengguna.
     */
    /**
     * Get KYC document detail for a user.
     * @tags KYC Verification
     */
    public function show(string $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return $this->errorResponse('Pengguna tidak ditemukan.', JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->successResponse('Detail dokumen KYC berhasil diambil.', new KycResource($user));
    }

    /**
     * Setujui KYC → status menjadi 'verified'.
     */
    /**
     * Approve pending KYC verification.
     * @tags KYC Verification
     */
    public function approve(string $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return $this->errorResponse('Pengguna tidak ditemukan.', JsonResponse::HTTP_NOT_FOUND);
        }

        if ($user->status_verification !== 'pending') {
            return $this->errorResponse('Hanya pengguna dengan status pending yang dapat disetujui.', JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->update([
            'status_verification' => 'verified',
            'rejection_feedback' => null,
        ]);

        return $this->successResponse('KYC pengguna berhasil disetujui.', new KycResource($user->fresh()));
    }

    /**
     * Tolak KYC → status menjadi 'rejected' + catatan revisi wajib.
     */
    /**
     * Reject pending KYC with feedback.
     * @tags KYC Verification
     */
    public function reject(RejectKycRequest $request, string $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return $this->errorResponse('Pengguna tidak ditemukan.', JsonResponse::HTTP_NOT_FOUND);
        }

        if ($user->status_verification !== 'pending') {
            return $this->errorResponse('Hanya pengguna dengan status pending yang dapat ditolak.', JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->update([
            'status_verification' => 'rejected',
            'rejection_feedback' => $request->validated('rejection_feedback'),
        ]);

        return $this->successResponse('KYC pengguna berhasil ditolak.', new KycResource($user->fresh()));
    }
}
