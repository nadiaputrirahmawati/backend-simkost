<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\OwnerSubmitKycRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerKYCController extends Controller
{
    use ApiResponse;

    /**
     * Get current KYC status.
     * @tags Owner KYC
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse('Status KYC berhasil diambil.', [
            'no_ktp' => $user->no_ktp,
            'npwp' => $user->npwp,
            'ktp_picture' => $user->ktp_picture,
            'ktp_picture_person' => $user->ktp_picture_person,
            'status_verification' => $user->status_verification,
            'rejection_feedback' => $user->rejection_feedback,
        ]);
    }

    /**
     * Submit KYC documents. Sets status to 'pending'.
     * @tags Owner KYC
     */
    public function submit(OwnerSubmitKycRequest $request): JsonResponse
    {
        $user = $request->user();

        // Deny if already pending or verified
        if (in_array($user->status_verification, ['pending', 'verified'])) {
            return $this->errorResponse(
                $user->status_verification === 'pending'
                    ? 'KYC sedang dalam proses verifikasi.'
                    : 'Akun sudah terverifikasi.',
                422,
            );
        }

        // Store uploaded files
        $ktpPath = $request->file('ktp_picture')->store('kyc', 'public');
        $ktpPersonPath = $request->file('ktp_picture_person')->store('kyc', 'public');

        $user->update([
            'no_ktp' => $request->no_ktp,
            'npwp' => $request->npwp,
            'ktp_picture' => $ktpPath,
            'ktp_picture_person' => $ktpPersonPath,
            'status_verification' => 'pending',
            'rejection_feedback' => null,
        ]);

        return $this->successResponse('Dokumen KYC berhasil dikirim. Menunggu verifikasi admin.', [
            'status_verification' => $user->fresh()->status_verification,
        ]);
    }
}
