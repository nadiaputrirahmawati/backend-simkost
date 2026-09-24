<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListPaymentsRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AdminPaymentController extends Controller
{
    use ApiResponse;

    /**
     * Monitor transaksi pembayaran dengan filter status & pencarian order_id.
     */
    /**
     * List payments with optional status and order_id filter.
     * @tags Payment Monitoring
     */
    public function index(ListPaymentsRequest $request): JsonResponse
    {
        $query = Payment::with(['user', 'contract']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('order_id')) {
            $query->where('order_id', 'like', '%' . $request->order_id . '%');
        }

        $payments = $query->latest()->paginate($request->input('per_page', 15));

        return $this->paginateResponse(
            PaymentResource::collection($payments),
            'Daftar transaksi pembayaran berhasil diambil.',
        );
    }

    /**
     * Detail transaksi pembayaran.
     */
    /**
     * Get payment detail with user and contract.
     * @tags Payment Monitoring
     */
    public function show(string $id): JsonResponse
    {
        $payment = Payment::with(['user', 'contract'])->find($id);

        if (! $payment) {
            return $this->errorResponse('Transaksi pembayaran tidak ditemukan.', JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->successResponse(
            'Detail transaksi pembayaran berhasil diambil.',
            new PaymentResource($payment),
        );
    }
}
