<?php

namespace App\Http\Controllers;

use App\Models\{Block, Contract, Payment, PaymentSchedule, ActivityLog};
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $blocks = Block::active()
            ->withCount([
                'apartments',
                'apartments as free_count'     => fn ($q) => $q->where('status', 'free'),
                'apartments as sold_count'     => fn ($q) => $q->where('status', 'sold'),
                'apartments as reserved_count' => fn ($q) => $q->where('status', 'reserved'),
            ])
            ->get();

        $stats = [
            'total_contracts'   => Contract::count(),
            'active_contracts'  => Contract::where('status', 'active')->count(),
            'total_received'    => Payment::where('type', '!=', 'refund')->sum('amount'),
            'total_debt'        => Contract::whereIn('status', ['draft', 'active'])->sum('debt_amount'),
            'overdue_schedules' => PaymentSchedule::where('status', 'overdue')->count(),
        ];

        $recentPayments = Payment::with([
            'contract.client',
            'contract.apartment.block',
            'receivedBy',
        ])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $overdueSchedules = PaymentSchedule::with([
            'contract.client',
            'contract.apartment.block',
        ])
            ->where('status', 'overdue')
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        $recentActivity = ActivityLog::with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $monthlyStats = Payment::selectRaw('
                YEAR(payment_date)  as yr,
                MONTH(payment_date) as mo,
                SUM(amount)         as total
            ')
            ->where('payment_date', '>=', now()->subMonths(6))
            ->where('type', '!=', 'refund')
            ->groupBy('yr', 'mo')
            ->orderBy('yr')
            ->orderBy('mo')
            ->get();

        return view('dashboard.index', compact(
            'blocks', 'stats', 'recentPayments',
            'overdueSchedules', 'recentActivity', 'monthlyStats'
        ));
    }
}
