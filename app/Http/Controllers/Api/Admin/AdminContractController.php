<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListContractsRequest;
use App\Http\Resources\ContractResource;
use App\Models\Contract;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AdminContractController extends Controller
{
    use ApiResponse;

    /**
     * Audit riwayat kontrak sewa dengan filter status & eager loading relasi.
     */
    /**
     * List contracts with optional status filter.
     * @tags Contract Audit
     */
    public function index(ListContractsRequest $request): JsonResponse
    {
        $query = Contract::with(['owner', 'user', 'room.kost'])
            ->withCount('payments');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $contracts = $query->latest()->paginate($request->input('per_page', 15));

        return $this->paginateResponse(
            ContractResource::collection($contracts),
            'Daftar riwayat kontrak sewa berhasil diambil.',
        );
    }

    /**
     * Detail kontrak sewa beserta riwayat pembayaran.
     */
    /**
     * Get contract detail with payment history.
     * @tags Contract Audit
     */
    public function show(string $id): JsonResponse
    {
        $contract = Contract::with([
            'owner',
            'user',
            'room.kost',
            'payments' => fn ($q) => $q->latest(),
        ])->withCount('payments')->find($id);

        if (! $contract) {
            return $this->errorResponse('Kontrak sewa tidak ditemukan.', JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->successResponse(
            'Detail kontrak sewa berhasil diambil.',
            new ContractResource($contract),
        );
    }
}
