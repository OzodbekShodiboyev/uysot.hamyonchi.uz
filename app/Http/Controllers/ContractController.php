<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contract\StoreContractRequest;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Models\{Block, Client, Contract, User};
use App\Services\ContractService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request, Response};
use Illuminate\View\View;

class ContractController extends Controller
{
    public function __construct(private readonly ContractService $service)
    {
    }

    public function index(Request $request): View
    {
        $contracts = Contract::with(['apartment.block', 'client', 'manager'])
            ->when($request->status,   fn ($q) => $q->where('status', $request->status))
            ->when($request->block_id, fn ($q) =>
                $q->whereHas('apartment', fn ($a) => $a->where('block_id', $request->block_id)))
            ->when($request->pay_type, fn ($q) => $q->where('payment_type', $request->pay_type))
            ->when($request->year,     fn ($q) => $q->where('contract_year', $request->year))
            ->when($request->search,   fn ($q) => $q->search($request->search))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $blocks = Block::active()->get();
        $years  = Contract::distinct()
            ->orderByDesc('contract_year')
            ->pluck('contract_year');

        return view('contracts.index', compact('contracts', 'blocks', 'years'));
    }

    public function create(Request $request): View
    {
        $apartment = $request->apartment_id
            ? \App\Models\Apartment::with('block')->find($request->apartment_id)
            : null;

        $clients  = Client::orderBy('full_name')->get();
        $managers = User::whereIn('role', ['admin', 'manager'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $blocks   = Block::active()
            ->with(['apartments' => fn ($q) =>
                $q->whereIn('status', ['free', 'reserved'])
                  ->select('id', 'block_id', 'number', 'floor', 'rooms', 'area_total', 'total_price', 'price_podklyuch', 'status')
                  ->orderBy('floor')
                  ->orderBy('number')
            ])
            ->get();

        return view('contracts.create', compact('apartment', 'clients', 'managers', 'blocks'));
    }

    public function store(StoreContractRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $contract = $this->service->create($request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success'         => true,
                    'contract_number' => $contract->contract_number,
                    'redirect'        => route('contracts.show', $contract),
                ]);
            }

            return redirect()
                ->route('contracts.show', $contract)
                ->with('success', "Shartnoma №{$contract->contract_number} muvaffaqiyatli yaratildi!");

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function show(Contract $contract): View
    {
        $contract->load([
            'apartment.block',
            'apartment.owner',
            'client',
            'manager',
            'accountant',
            'paymentSchedules',
            'payments.receivedBy',
            'activityLogs.user',
        ]);

        return view('contracts.show', compact('contract'));
    }

    public function activate(Contract $contract): JsonResponse
    {
        try {
            $this->service->activate($contract);

            return response()->json([
                'success' => true,
                'message' => "Shartnoma №{$contract->contract_number} muvaffaqiyatli faollashtirildi.",
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function cancel(Contract $contract, Request $request): JsonResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        try {
            $this->service->cancel($contract, $request->reason);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // To'lov qabul qilish
    public function storePayment(Contract $contract, StorePaymentRequest $request): JsonResponse
    {
        try {
            $payment = $this->service->receivePayment($contract, $request->validated());

            return response()->json([
                'success'        => true,
                'receipt_number' => $payment->receipt_number,
                'message'        => "To'lov qabul qilindi: {$payment->formatted_amount} — {$payment->receipt_number}",
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // PDF chop etish
    public function print(Contract $contract): Response
    {
        $contract->load([
            'apartment.block',
            'apartment.owner',
            'client',
            'manager',
            'paymentSchedules',
        ]);

        $pdf = Pdf::loadView('contracts.pdf', compact('contract'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream("shartnoma-{$contract->contract_number}.pdf");
    }
}
