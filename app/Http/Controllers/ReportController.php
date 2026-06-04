<?php

namespace App\Http\Controllers;

use App\Models\{Block, Payment, User};
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $year = (int) ($request->year ?? now()->year);

        $blockSales = Block::active()
            ->withCount([
                'apartments as sold' => fn ($q) => $q->where('status', 'sold'),
            ])
            ->with(['apartments:id,block_id,total_price,status'])
            ->get()
            ->map(fn ($b) => [
                'block'   => $b,
                'sold'    => $b->sold,
                'revenue' => $b->apartments->where('status', 'sold')->sum('total_price'),
                'stats'   => $b->stats(),
            ]);

        $monthly = Payment::selectRaw('MONTH(payment_date) as month, SUM(amount) as total')
            ->whereYear('payment_date', $year)
            ->where('type', '!=', 'refund')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $managerStats = User::whereIn('role', ['admin', 'manager'])
            ->where('is_active', true)
            ->withCount([
                'contracts as total_contracts',
                'contracts as active_contracts' => fn ($q) => $q->where('status', 'active'),
            ])
            ->orderByDesc('total_contracts')
            ->get();

        return view('reports.index', compact('blockSales', 'monthly', 'managerStats', 'year'));
    }

    public function exportExcel(Request $request)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ContractsExport($request->all()),
            'shartnomalar-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
