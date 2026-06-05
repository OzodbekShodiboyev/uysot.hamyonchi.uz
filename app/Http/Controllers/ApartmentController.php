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
            'total_price'          => ['sometimes', 'numeric', 'min:1000'],
            'price_podklyuch'      => ['sometimes', 'nullable', 'numeric', 'min:1000'],
            'price_karobka_full'   => ['sometimes', 'nullable', 'numeric', 'min:1000'],
            'price_podklyuch_full' => ['sometimes', 'nullable', 'numeric', 'min:1000'],
            'renovation'           => ['sometimes', 'in:none,rough,full'],
            'price_per_m2'         => ['sometimes', 'numeric', 'min:0'],
            'notes'                => ['nullable', 'string'],
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

    // Ko'p qavatlar uchun bir vaqtda xonadon yaratish (admin)
    public function bulkStore(Request $request): JsonResponse
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Faqat admin uchun.'], 403);
        }

        $data = $request->validate([
            'block_id'          => ['required', 'integer', 'exists:blocks,id'],
            'floor_from'           => ['required', 'integer', 'min:1', 'max:100'],
            'floor_to'             => ['required', 'integer', 'min:1', 'max:100', 'gte:floor_from'],
            'first_apt_floor'      => ['required', 'integer', 'min:1', 'max:100', 'lte:floor_from'],
            'apts_per_floor'       => ['required', 'integer', 'min:1', 'max:20'],
            'position'             => ['required', 'integer', 'min:1', 'lte:apts_per_floor'],
            'entrance'          => ['nullable', 'integer', 'min:1'],
            'rooms'             => ['required', 'integer', 'min:1', 'max:10'],
            'area_total'        => ['required', 'numeric', 'min:10'],
            'area_living'       => ['nullable', 'numeric', 'min:1'],
            'area_kitchen'      => ['nullable', 'numeric', 'min:1'],
            'total_price'          => ['required', 'numeric', 'min:1000'],
            'price_podklyuch'      => ['nullable', 'numeric', 'min:1000'],
            'price_karobka_full'   => ['nullable', 'numeric', 'min:1000'],
            'price_podklyuch_full' => ['nullable', 'numeric', 'min:1000'],
            'price_per_m2'         => ['nullable', 'numeric', 'min:0'],
        ]);

        $created      = [];
        $skipped      = [];
        $perFloor     = (int) $data['apts_per_floor'];
        $position     = (int) $data['position'];
        $firstAptFloor = (int) $data['first_apt_floor'];

        try {
            for ($floor = $data['floor_from']; $floor <= $data['floor_to']; $floor++) {
                // Raqam = (qavat - birinchi_turar_qavat) × qavatdagi_jami + pozitsiya
                $number = (string) (($floor - $firstAptFloor) * $perFloor + $position);

                if (Apartment::where('block_id', $data['block_id'])->where('number', $number)->exists()) {
                    $skipped[] = $number;
                    continue;
                }

                Apartment::create([
                    'block_id'             => $data['block_id'],
                    'number'               => $number,
                    'floor'                => $floor,
                    'entrance'             => $data['entrance'] ?? 1,
                    'rooms'                => $data['rooms'],
                    'area_total'           => $data['area_total'],
                    'total_price'          => $data['total_price'],
                    'price_podklyuch'      => !empty($data['price_podklyuch']) ? $data['price_podklyuch'] : null,
                    'price_karobka_full'   => !empty($data['price_karobka_full']) ? $data['price_karobka_full'] : null,
                    'price_podklyuch_full' => !empty($data['price_podklyuch_full']) ? $data['price_podklyuch_full'] : null,
                    'renovation'           => 'none',
                    'status'               => 'free',
                ]);

                $created[] = $number;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Xatolik: ' . $e->getMessage(),
            ], 500);
        }

        if ($created) {
            ActivityLog::log(
                'apartment.bulk_created',
                Block::find($data['block_id']),
                count($created) . " ta xonadon yaratildi"
            );
        }

        $msg = count($created) . " ta xonadon yaratildi.";
        if ($skipped) {
            $msg .= " Mavjud (o'tkazildi): " . implode(', ', array_slice($skipped, 0, 10));
            if (count($skipped) > 10) $msg .= '...';
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
            'created' => count($created),
            'skipped' => $skipped,
        ]);
    }

    // Xonadonni o'chirish (faqat admin, faqat shartnomasiz)
    public function destroy(Apartment $apartment): JsonResponse
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Faqat admin uchun.'], 403);
        }

        if ($apartment->contracts()->exists()) {
            return response()->json([
                'success' => false,
                'message' => "#{$apartment->number} xonadonda shartnoma mavjud. O'chirib bo'lmaydi.",
            ], 422);
        }

        $number = $apartment->number;
        $apartment->delete();

        return response()->json([
            'success' => true,
            'message' => "#{$number} xonadon o'chirildi.",
        ]);
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

    // Xonadonni "Sotilgan" deb belgilash (admin, shartnomasiz)
    public function markSold(Apartment $apartment): JsonResponse
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Faqat admin uchun.'], 403);
        }

        $apartment->update(['status' => 'sold']);

        ActivityLog::log(
            'apartment.marked_sold',
            $apartment,
            "Xonadon #{$apartment->number} sotilgan deb belgilandi (shartnomasiz)"
        );

        return response()->json([
            'success' => true,
            'message' => "#{$apartment->number} sotilgan deb belgilandi.",
        ]);
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
