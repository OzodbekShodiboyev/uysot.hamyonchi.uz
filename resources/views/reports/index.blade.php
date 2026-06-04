@extends('layouts.app')
@section('title', 'Hisobotlar')
@section('heading', 'Hisobotlar')
@section('subheading', $year . '-yil · Umumiy natijalar')

@section('header-actions')
<form method="GET" class="flex items-center gap-2">
    <select name="year" class="form-input w-28 py-1.5 text-sm" onchange="this.form.submit()">
        @foreach(range(now()->year, 2020, -1) as $y)
        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}-yil</option>
        @endforeach
    </select>
</form>
<a href="{{ route('reports.excel', ['year' => $year]) }}" class="btn-secondary">
    <i class="fa-solid fa-file-excel text-green-600"></i> Excel yuklab olish
</a>
@endsection

@section('content')
<div class="space-y-6">

{{-- ── Bloklar bo'yicha sotuv ──────────────────────────────────── --}}
<div>
    <h2 class="font-semibold text-sm text-gray-500 uppercase tracking-wide mb-3">
        Bloklar bo'yicha sotuv
    </h2>
    <div class="grid grid-cols-3 gap-4">
        @foreach($blockSales as $bs)
        @php
        $total   = $bs['stats']['total'] ?: 1;
        $soldPct = round(($bs['stats']['sold'] / $total) * 100);
        @endphp
        <div class="card p-5">
            <div class="flex items-center gap-2.5 mb-4">
                <span class="w-3 h-3 rounded-full flex-shrink-0"
                      style="background:{{ $bs['block']->color }}"></span>
                <span class="font-bold">{{ $bs['block']->name }}</span>
                <a href="{{ route('apartments.block', $bs['block']) }}"
                   class="ml-auto text-xs text-blue-600 hover:underline">Ko'rish →</a>
            </div>

            {{-- SVG donut --}}
            <div class="flex items-center gap-4 mb-4">
                <div class="relative w-16 h-16 flex-shrink-0">
                    <svg class="w-16 h-16 -rotate-90" viewBox="0 0 36 36">
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#f3f4f6" stroke-width="3.5"/>
                        <circle cx="18" cy="18" r="15.9" fill="none"
                                stroke="{{ $bs['block']->color }}" stroke-width="3.5"
                                stroke-dasharray="{{ $soldPct }} {{ 100 - $soldPct }}"
                                stroke-linecap="round"/>
                    </svg>
                    <span class="absolute inset-0 flex items-center justify-center
                                 text-xs font-bold text-gray-700">{{ $soldPct }}%</span>
                </div>
                <div class="flex-1 space-y-1.5 text-xs">
                    @foreach([
                        ['Jami',     $bs['stats']['total'],   'text-gray-700'],
                        ['Bo\'sh',   $bs['stats']['free'],    'text-emerald-700'],
                        ['Band',     $bs['stats']['reserved'],'text-amber-700'],
                        ['Sotilgan', $bs['stats']['sold'],    'text-red-700'],
                    ] as [$l, $v, $c])
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ $l }}</span>
                        <span class="font-semibold {{ $c }}">{{ $v }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="border-t border-gray-100 pt-3">
                <p class="text-xs text-gray-400 mb-0.5">Sotuv daromadi</p>
                <p class="text-lg font-bold text-emerald-700">
                    {{ number_format($bs['revenue']) }} so'm
                </p>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ── Oylik to'lovlar grafigi ─────────────────────────────────── --}}
<div class="card p-5">
    <h2 class="font-semibold text-sm text-gray-500 uppercase tracking-wide mb-4">
        {{ $year }}-yil oylik to'lovlar
    </h2>
    @php
    $months = ['Yan','Feb','Mar','Apr','May','Iyn','Iyl','Avg','Sen','Okt','Noy','Dek'];
    $maxVal = $monthly->max() ?: 1;
    $total  = $monthly->sum();
    @endphp
    <div class="flex items-end gap-2 h-44 mb-2">
        @for($m = 1; $m <= 12; $m++)
        @php $val = $monthly[$m] ?? 0; $pct = $maxVal > 0 ? ($val / $maxVal * 100) : 0; @endphp
        <div class="flex-1 flex flex-col items-center gap-1">
            <span class="text-xs text-gray-500 font-medium whitespace-nowrap"
                  style="font-size:10px">
                @if($val > 0) {{ number_format($val/1000000,1) }}M @endif
            </span>
            <div class="w-full flex items-end justify-center rounded-t"
                 style="height:120px">
                @if($val > 0)
                <div class="w-full rounded-t transition-all"
                     style="height:{{ max(4, $pct) }}%;
                            background:{{ $m == now()->month ? '#1D9E75' : '#9FE1CB' }};
                            min-height:4px"></div>
                @else
                <div class="w-full rounded-t bg-gray-100" style="height:4px"></div>
                @endif
            </div>
            <span class="text-gray-400 whitespace-nowrap" style="font-size:10px">
                {{ $months[$m-1] }}
            </span>
        </div>
        @endfor
    </div>
    <div class="text-right text-sm text-gray-500 border-t border-gray-100 pt-3">
        {{ $year }}-yil jami:
        <strong class="text-gray-800 ml-1">{{ number_format($total) }} so'm</strong>
    </div>
</div>

{{-- ── Menejerlar natijalari ───────────────────────────────────── --}}
<div>
    <h2 class="font-semibold text-sm text-gray-500 uppercase tracking-wide mb-3">
        Menejerlar natijalari
    </h2>
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase border-b border-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Menejer</th>
                    <th class="px-4 py-3 text-left font-semibold">Lavozim</th>
                    <th class="px-4 py-3 text-center font-semibold">Jami shartnoma</th>
                    <th class="px-4 py-3 text-center font-semibold">Faol</th>
                    <th class="px-4 py-3 text-right font-semibold">Jami sotuv</th>
                    <th class="px-4 py-3 text-right font-semibold">Qabul qilingan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($managerStats as $mgr)
                @php
                $revenue  = $mgr->contracts()->sum('final_price');
                $received = $mgr->payments()->where('type','!=','refund')->sum('amount');
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center
                                        text-xs font-bold text-emerald-700 flex-shrink-0">
                                {{ $mgr->initials }}
                            </div>
                            <span class="font-semibold text-xs">{{ $mgr->name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $mgr->role_label }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7
                                     bg-gray-100 rounded-full text-xs font-bold text-gray-700">
                            {{ $mgr->total_contracts }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7
                                     bg-blue-50 rounded-full text-xs font-bold text-blue-700">
                            {{ $mgr->active_contracts }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right text-xs font-semibold">
                        {{ number_format($revenue) }} so'm
                    </td>
                    <td class="px-4 py-3 text-right text-xs font-bold text-emerald-700">
                        {{ number_format($received) }} so'm
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-xs text-gray-400">
                        Ma'lumot topilmadi
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

</div>
@endsection
