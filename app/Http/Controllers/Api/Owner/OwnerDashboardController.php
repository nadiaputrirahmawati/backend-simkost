<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OwnerDashboardController extends Controller
{
    use ApiResponse;

    /**
     * Owner dashboard: aggregated metrics for the authenticated owner's kost portfolio.
     * @tags Owner Dashboard
     */
    public function index(): JsonResponse
    {
        $ownerId = auth()->id();

        $data = DB::transaction(fn () => [
            'occupancy'     => $this->occupancy($ownerId),
            'active_tenants' => $this->activeTenants($ownerId),
            'financial'     => $this->financial($ownerId),
            'expiring_contracts' => $this->expiringContracts($ownerId),
            'pending_complaints' => $this->pendingComplaints($ownerId),
        ]);

        return $this->successResponse('Owner dashboard data retrieved.', $data);
    }

    private function occupancy(string $ownerId): array
    {
        $kostIds = DB::table('kosts')->where('owner_id', $ownerId)->pluck('id');

        $breakdown = Room::query()
            ->whereIn('kost_id', $kostIds)
            ->selectRaw("status, COUNT(*) as count")
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        $total     = array_sum($breakdown);
        $occupied  = $breakdown['occupied'] ?? 0;

        return [
            'total_rooms'    => $total,
            'occupied'       => $occupied,
            'available'      => $breakdown['available'] ?? 0,
            'maintenance'    => $breakdown['maintenance'] ?? 0,
            'occupancy_rate' => $total > 0 ? round($occupied / $total * 100, 2) : 0,
        ];
    }

    /**
     * Distinct tenants with at least one active contract on the owner's rooms.
     */
    private function activeTenants(string $ownerId): int
    {
        return Contract::query()
            ->where('owner_id', $ownerId)
            ->where('status', 'active')
            ->distinct('user_id')
            ->count('user_id');
    }

    private function financial(string $ownerId): array
    {
        $estimatedIncome = Contract::query()
            ->where('owner_id', $ownerId)
            ->where('status', 'active')
            ->selectRaw('COALESCE(SUM(monthly_price), 0) as total')
            ->value('total');

        $balance = User::where('id', $ownerId)->value('balance');

        return [
            'estimated_monthly_income' => (float) $estimatedIncome,
            'available_balance'        => (float) $balance,
        ];
    }

    /**
     * Active contracts expiring within H-7 or H-30 from now.
     */
    private function expiringContracts(string $ownerId): array
    {
        $today = Carbon::today();

        $h7  = Contract::query()
            ->where('owner_id', $ownerId)
            ->where('status', 'active')
            ->whereBetween('end_date', [$today, $today->copy()->addDays(7)])
            ->count();

        $h30 = Contract::query()
            ->where('owner_id', $ownerId)
            ->where('status', 'active')
            ->whereBetween('end_date', [$today, $today->copy()->addDays(30)])
            ->count();

        return [
            'expiring_h7'  => $h7,
            'expiring_h30' => $h30,
        ];
    }

    /**
     * Open complaints (sent_in or in_process) on the owner's rooms.
     */
    private function pendingComplaints(string $ownerId): int
    {
        $roomIds = DB::table('rooms')
            ->join('kosts', 'rooms.kost_id', '=', 'kosts.id')
            ->where('kosts.owner_id', $ownerId)
            ->pluck('rooms.id');

        return Complaint::query()
            ->whereIn('room_id', $roomIds)
            ->whereIn('status', ['sent_in', 'in_process'])
            ->count();
    }
}
