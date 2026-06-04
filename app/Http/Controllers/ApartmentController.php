<?php

namespace App\Http\Controllers;

use App\Http\Requests\Apartment\StoreApartmentRequest;
use App\Models\{ActivityLog, Apartment, Block};
use App\Services\ContractService;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\View\View;

class ApartmentController extends Controller
{
    public function __construct(private readonly ContractService $contractService)
    {
    }

    // Barcha bloklar umumiy ko'rinishi
    public function index(): View
    {
        $blocks = Block::active()
            ->with(['apartments' => fn ($q) => $q->orderBy('floor')->orderBy('number')])
            ->get();

        return view('apartments.index', compact('blocks'));
    }

    // Bitta blok — qavatma-qavat ko'rinishi
    public function block(Block $block): View
    {
        $apartments = Apartment::where('block_id', $block->id)
            ->with(['owner', 'activeContract.client'])
            ->orderBy('floor')
            ->orderBy('number')
            ->get()
            ->groupBy('floor');

        $stats  = $block->stats();
        $floors = $apartments->keys()->sortDesc()->values();
        $blocks = Block::active()->get(); // Sidebar navigatsiyasi uchun

        return view('apartments.block', compact('block', 'apartments', 'stats', 'floors', 'blocks'));
    }

    // AJAX: blok holati (polling/fallback)
    public function statusApi(Block $block): JsonResponse
    {
        $apts = Apartment::where('block_id', $block->id)
            ->select('id', 'number', 'floor', 'status', 'reserved_until', 'total_price', 'renovation')
            ->get()
            ->keyBy('id');

        return response()->json([
            'apartments' => $apts,
            'stats'      => $block->stats(),
            'ts'         => now()->toISOString(),
        ]);
    }

    // AJAX: xonadon tafsiloti paneli uchun
    public function detail(Apartment $apartment): JsonResponse
    {
        $apartment->load([
            'block', 'owner',
            'activeContract.client',
            'activeContract.paymentSchedules',
            'activeContract.payments.receivedBy',
            'activeContract.manager',
        ]);

        return response()->json($apartment);
    }

    // Yangi xonadon qo'shish
    public function store(StoreApartmentRequest $request): RedirectResponse
    {
        $exists = Apartment::where('block_id', $request->block_id)
            ->where('number', $request->number)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'number' => "Bu blokda #{$request->number} raqamli xonadon allaqachon mavjud.",
            ])->withInput();
        }

        $apt = Apartment::create($request->validated());

        ActivityLog::log(
            'apartment.created',
            $apt,
            "Yangi xonadon qo'shildi: {$apt->full_name}"
        );

        return redirect()->route('apartments.block', $apt->block_id)
            ->with('success', "Xonadon #{$apt->number} muvaffaqiyatli qo'shildi.");
    }

    // Xonadon ma'lumotlarini yangilash
    public function update(Request $request, Apartment $apartment): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'rooms'           => ['sometimes', 'integer', 'min:1', 'max:10'],
            'area_total'      => ['sometimes', 'numeric', 'min:10'],
            'total_price'     => ['sometimes', 'numeric', 'min:1000'],
            'price_podklyuch' => ['sometimes', 'nullable', 'numeric', 'min:1000'],
            'renovation'      => ['sometimes', 'in:none,rough,full'],
            'price_per_m2'    => ['sometimes', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string'],
        ]);

        $old = $apartment->only(array_keys($data));
        $apartment->update($data);

        ActivityLog::log(
            'apartment.updated',
            $apartment,
            "Xonadon #{$apartment->number} yangilandi",
            $old,
            $data
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'apartment' => $apartment->fresh()]);
        }

        return back()->with('success', 'Xonadon yangilandi.');
    }

    // Vaqtinchalik band qilish
    public function reserve(Apartment $apartment, Request $request): JsonResponse
    {
        $request->validate(['hours' => ['nullable', 'integer', 'min:1', 'max:72']]);

        try {
            $hours = (int) ($request->hours ?? 24);
            $this->contractService->reserve($apartment, auth()->id(), $hours);

            return response()->json([
                'success' => true,
                'message' => "Xonadon #{$apartment->number} {$hours} soatga band qilindi.",
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // Bandlikni bekor qilish
    public function release(Apartment $apartment): JsonResponse
    {
        try {
            $this->contractService->release($apartment);

            return response()->json([
                'success' => true,
                "message" => "Xonadon #{$apartment->number} bo'shatildi.",
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
