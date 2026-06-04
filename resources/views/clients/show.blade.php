@extends('layouts.app')
@section('title', $client->full_name)
@section('heading', $client->full_name)
@section('subheading', $client->phone . ' · ' . $client->contracts->count() . ' ta shartnoma')

@section('header-actions')
<a href="{{ route('contracts.create') }}" class="btn-primary">
    <i class="fa-solid fa-file-contract"></i> Yangi shartnoma
</a>
@endsection

@section('content')
<div class="grid grid-cols-3 gap-5">

{{-- ── CHAP: profil ───────────────────────────────────────────── --}}
<div class="space-y-4">

    <div class="card p-5">
        <div class="flex items-center gap-4 mb-4">
            <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center
                        text-xl font-bold text-blue-700 flex-shrink-0">
                {{ $client->initials }}
            </div>
            <div>
                <h3 class="font-bold text-base">{{ $client->full_name }}</h3>
                @if($client->age)
                <p class="text-sm text-gray-500 mt-0.5">{{ $client->age }} yosh</p>
                @endif
            </div>
        </div>
        <div class="space-y-2 text-sm">
            @foreach([
                ['fa-phone',       $client->phone],
                ['fa-phone',       $client->phone_extra],
                ['fa-id-card',     $client->passport_series ? $client->passport_series . ($client->passport_issued_date ? ' (' . $client->passport_issued_date->format('d.m.Y') . ')' : '') : null],
                ['fa-fingerprint', $client->pinfl ? 'PINFL: ' . $client->pinfl : null],
                ['fa-cake-candles',$client->birth_date ? $client->birth_date->format('d.m.Y') : null],
                ['fa-location-dot',$client->address],
                ['fa-briefcase',   $client->workplace . ($client->position ? ' · ' . $client->position : '')],
            ] as [$icon, $val])
            @if($val)
            <div class="flex items-start gap-2 text-gray-600">
                <i class="fa-solid {{ $icon }} text-gray-300 w-4 text-center mt-0.5 flex-shrink-0"></i>
                <span class="flex-1">{{ $val }}</span>
            </div>
            @endif
            @endforeach
        </div>
    </div>

    {{-- Statistika --}}
    @php
    $totalPaid = $client->contracts->sum('paid_amount');
    $totalDebt = $client->contracts->sum('debt_amount');
    $activeCnt = $client->contracts->where('status','active')->count();
    $doneCnt   = $client->contracts->where('status','completed')->count();
    @endphp
    <div class="card p-4">
        <p class="text-xs text-gray-400 font-semibold mb-3 uppercase tracking-wide">Statistika</p>
        <div class="space-y-2.5 text-sm">
            @foreach([
                ['Jami shartnoma',   $client->contracts->count(), 'text-gray-700'],
                ['Faol',             $activeCnt,                  'text-blue-700'],
                ['Yakunlangan',      $doneCnt,                    'text-emerald-700'],
                ["To'langan jami",   number_format($totalPaid)." so'm", 'text-emerald-700'],
                ['Qarz',             number_format($totalDebt)." so'm", $totalDebt > 0 ? 'text-red-600' : 'text-gray-400'],
            ] as [$lbl, $val, $cls])
            <div class="flex justify-between">
                <span class="text-gray-500">{{ $lbl }}</span>
                <span class="font-semibold {{ $cls }}">{{ $val }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Tahrirlash --}}
    <div class="card p-4">
        <button onclick="openEditModal()" class="btn-secondary w-full justify-center">
            <i class="fa-solid fa-pen"></i> Ma'lumotlarni tahrirlash
        </button>
    </div>

</div>

{{-- ── O'NG: shartnomalar (2/3) ───────────────────────────────── --}}
<div class="col-span-2 space-y-3">
    <h3 class="font-semibold text-sm text-gray-500 uppercase tracking-wide">
        Shartnomalar tarixi
    </h3>

    @forelse($client->contracts as $c)
    @php
    $prog = $c->progress_percent;
    $sCls = match($c->status) {
        'draft'     => 'bg-amber-100 text-amber-700',
        'active'    => 'bg-blue-100 text-blue-700',
        'completed' => 'bg-emerald-100 text-emerald-700',
        'cancelled' => 'bg-red-100 text-red-700',
        default     => 'bg-gray-100 text-gray-600',
    };
    @endphp
    <div class="card p-4 hover:border-gray-200 transition">
        <div class="flex items-start justify-between mb-3">
            <div>
                <a href="{{ route('contracts.show', $c) }}"
                   class="font-mono font-bold text-blue-700 hover:underline text-sm">
                    {{ $c->contract_number }}
                </a>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ $c->apartment->block->name }} ·
                    {{ $c->apartment->number }}-xonadon ·
                    {{ $c->apartment->floor }}-qavat ·
                    {{ $c->apartment->rooms }} xona
                </p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $sCls }}">
                    {{ $c->status_label }}
                </span>
                <a href="{{ route('contracts.show', $c) }}"
                   class="text-xs text-blue-600 hover:underline">Ko'rish →</a>
            </div>
        </div>
        <div class="grid grid-cols-4 gap-3 text-sm mb-3">
            <div>
                <p class="text-xs text-gray-400 mb-0.5">To'lov turi</p>
                <p class="font-semibold text-xs">{{ $c->payment_type_badge }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Yakuniy narx</p>
                <p class="font-semibold text-xs">{{ number_format($c->final_price) }} so'm</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">To'langan</p>
                <p class="font-semibold text-xs text-emerald-700">
                    {{ number_format($c->paid_amount) }} so'm
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Qarz</p>
                <p class="font-semibold text-xs {{ $c->debt_amount > 0 ? 'text-red-600' : 'text-gray-400' }}">
                    {{ number_format($c->debt_amount) }} so'm
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                <div class="{{ $prog >= 100 ? 'bg-emerald-500' : 'bg-blue-500' }} h-1.5 rounded-full"
                     style="width:{{ $prog }}%"></div>
            </div>
            <span class="text-xs text-gray-400">{{ $prog }}%</span>
        </div>
    </div>
    @empty
    <div class="card p-12 text-center text-gray-400">
        <i class="fa-solid fa-file-circle-xmark text-4xl block mb-3 text-gray-200"></i>
        Hali shartnoma yo'q
    </div>
    @endforelse
</div>

</div>
@endsection

@push('scripts')
<script>
function openEditModal() {
    openModal(`
    <div class="p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold">Mijozni tahrirlash</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form action="{{ route('clients.update', $client) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">To'liq ism *</label>
                    <input name="full_name" type="text" class="form-input" required
                           value="{{ $client->full_name }}">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Telefon *</label>
                    <input name="phone" type="tel" class="form-input" required
                           value="{{ $client->phone }}">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Qo'shimcha tel</label>
                    <input name="phone_extra" type="tel" class="form-input"
                           value="{{ $client->phone_extra }}">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Ish joyi</label>
                    <input name="workplace" type="text" class="form-input"
                           value="{{ $client->workplace }}">
                </div>
                <div class="col-span-2">
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Manzil</label>
                    <input name="address" type="text" class="form-input"
                           value="{{ $client->address }}">
                </div>
                <div class="col-span-2">
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Izoh</label>
                    <textarea name="notes" class="form-input resize-none" rows="2">{{ $client->notes }}</textarea>
                </div>
            </div>
            <div class="flex gap-3 justify-end pt-2">
                <button type="button" onclick="closeModal()" class="btn-secondary">Bekor</button>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Saqlash
                </button>
            </div>
        </form>
    </div>`);
}
</script>
@endpush
