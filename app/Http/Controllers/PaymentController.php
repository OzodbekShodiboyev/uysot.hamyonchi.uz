<?php

namespace App\Http\Controllers;

use App\Models\{Payment, PaymentSchedule};
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $payments = Payment::with([
            'contract.client',
            'contract.apartment.block',
            'receivedBy',
        ])
            ->when($request->method, fn ($q) => $q->where('payment_method', $request->method))
            ->when($request->type,   fn ($q) => $q->where('type', $request->type))
            ->when($request->from,   fn ($q) => $q->where('payment_date', '>=', $request->from))
            ->when($request->to,     fn ($q) => $q->where('payment_date', '<=', $request->to))
            ->when($request->search, fn ($q) =>
                $q->where('receipt_number', 'like', "%{$request->search}%")
                  ->orWhereHas('contract.client', fn ($c) =>
                      $c->where('full_name', 'like', "%{$request->search}%")
                        ->orWhere('phone', 'like', "%{$request->search}%")
                  )
            )
            ->orderByDesc('payment_date')
            ->paginate(25)
            ->withQueryString();

        $total = Payment::when($request->from, fn ($q) => $q->where('payment_date', '>=', $request->from))
            ->when($request->to, fn ($q) => $q->where('payment_date', '<=', $request->to))
            ->where('type', '!=', 'refund')
            ->sum('amount');

        return view('payments.index', compact('payments', 'total'));
    }

    public function overdue(): View
    {
        $schedules = PaymentSchedule::with([
            'contract.client',
            'contract.apartment.block',
        ])
            ->where('status', 'overdue')
            ->orderBy('due_date')
            ->paginate(20);

        return view('payments.overdue', compact('schedules'));
    }
}
