<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Kost;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use App\Models\Withdrawal;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    use ApiResponse;

    /**
     * Get aggregated dashboard data for admin panel.
     * @tags Dashboard
     */
    public function index(): JsonResponse
    {
        // Single DB transaction to keep all reads consistent
        $data = DB::transaction(function () {
            return [
                'financial'          => $this->financialSummary(),
                'queues'             => $this->queueCounts(),
                'entities'           => $this->entityCounts(),
                'occupancy'          => $this->occupancyRatio(),
                'monthly_transactions' => $this->monthlyTransactions(),
            ];
        });

        return $this->successResponse('Dashboard data retrieved.', $data);
    }

    /**
     * Total platform revenue: sum of completed payments.
     * ponytail: Use materialized view or cache with 5-min TTL when payment volume > 10k rows. Add when queries > 200ms.
     */
    private function financialSummary(): array
    {
        $revenue = Payment::query()
            ->where('status', 'completed')
            ->selectRaw('COALESCE(SUM(amount), 0) as total_revenue, COUNT(*) as total_transactions')
            ->first();

        $totalWithdrawn = Withdrawal::query()
            ->where('status', 'approved')
            ->selectRaw('COALESCE(SUM(amount), 0) as total_withdrawn, COUNT(*) as total_withdrawals')
            ->first();

        return [
            'total_revenue'      => (float) $revenue->total_revenue,
            'total_transactions' => (int) $revenue->total_transactions,
            'total_withdrawn'    => (float) $totalWithdrawn->total_withdrawn,
            'total_withdrawals'  => (int) $totalWithdrawn->total_withdrawals,
            'net_platform'       => (float) $revenue->total_revenue - (float) $totalWithdrawn->total_withdrawn,
        ];
    }

    /**
     * Pending withdrawal queue + pending KYC verification queue.
     */
    private function queueCounts(): array
    {
        $pendingWithdrawals = Withdrawal::query()
            ->where('status', 'pending')
            ->count();

        $pendingKyc = User::query()
            ->where('status_verification', 'pending')
            ->count();

        return [
            'pending_withdrawals' => $pendingWithdrawals,
            'pending_kyc'         => $pendingKyc,
        ];
    }

    /**
     * Counts for owners, active tenants, and kost properties.
     */
    private function entityCounts(): array
    {
        $totalOwners = User::query()->where('role', 'owner')->count();

        // Active tenants = distinct users with at least one active contract
        $activeTenants = Contract::query()
            ->where('status', 'active')
            ->distinct('user_id')
            ->count('user_id');

        $totalKosts = Kost::query()->count();

        return [
            'total_owners'    => $totalOwners,
            'active_tenants'  => $activeTenants,
            'total_kosts'     => $totalKosts,
        ];
    }

    /**
     * Room occupancy: occupied vs available vs maintenance.
     */
    private function occupancyRatio(): array
    {
        $breakdown = Room::query()
            ->selectRaw("status, COUNT(*) as count")
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        $total     = array_sum($breakdown);
        $occupied  = $breakdown['occupied'] ?? 0;
        $available = $breakdown['available'] ?? 0;

        return [
            'total_rooms'       => $total,
            'occupied'          => $occupied,
            'available'         => $available,
            'maintenance'       => $breakdown['maintenance'] ?? 0,
            'occupancy_rate'    => $total > 0 ? round($occupied / $total * 100, 2) : 0,
        ];
    }

    /**
     * Last 12 months of transaction data for chart consumption.
     * Returns [{month: "2026-09", revenue: 1500000, count: 12}, ...]
     */
    private function monthlyTransactions(): array
    {
        $start = Carbon::now()->subMonths(11)->startOfMonth();
        $end   = Carbon::now()->endOfMonth();

        $rows = Payment::query()
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$start, $end])
            ->select(
                DB::raw("TO_CHAR(payment_date, 'YYYY-MM') as month"),
                DB::raw('COALESCE(SUM(amount), 0) as revenue'),
                DB::raw('COUNT(*) as count'),
            )
            ->groupBy(DB::raw("TO_CHAR(payment_date, 'YYYY-MM')"))
            ->orderBy('month')
            ->get();

        // Fill gaps so frontend always gets 12 data points
        $map = $rows->pluck('revenue', 'month')->toArray();
        $cnt = $rows->pluck('count', 'month')->toArray();

        $result = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m');
            $result[] = [
                'month'   => $key,
                'revenue' => (float) ($map[$key] ?? 0),
                'count'   => (int) ($cnt[$key] ?? 0),
            ];
            $cursor->addMonth();
        }

        return $result;
    }
}
