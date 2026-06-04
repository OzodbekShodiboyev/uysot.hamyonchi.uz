@extends('layouts.app')
@section('title', 'Shartnomalar')
@section('heading', 'Shartnomalar')
@section('subheading', number_format($contracts->total()) . ' ta shartnoma')

@section('header-actions')
<a href="{{ route('contracts.create') }}" class="btn-primary">
    <i class="fa-solid fa-plus"></i> Yangi shartnoma
</a>
<a href="{{ route('reports.excel', request()->query()) }}" class="btn-secondary">
    <i class="fa-solid fa-file-excel text-green-600"></i> Excel
</a>
@endsection

@section('content')
<div class="space-y-4">

{{-- Filtrlar --}}
<form method="GET" class="card p-4 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-36">
        <label class="text-xs text-gray-500 block mb-1">Qidiruv</label>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Raqam, ism, telefon..."
               class="form-input">
    </div>
    <div class="w-36">
        <label class="text-xs text-gray-500 block mb-1">Blok</label>
        <select name="block_id" class="form-input">
            <option value="">Barcha bloklar</option>
            @foreach($blocks as $b)
            <option value="{{ $b->id }}" {{ request('block_id') == $b->id ? 'selected' : '' }}>
                {{ $b->name }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="w-36">
        <label class="text-xs text-gray-500 block mb-1">Holat</label>
        <select name="status" class="form-input">
            <option value="">Barcha holatlar</option>
            @foreach(['draft'=>'Loyiha','active'=>'Faol','completed'=>'Yakunlangan','cancelled'=>'Bekor'] as $v => $l)
            <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div class="w-44">
        <label class="text-xs text-gray-500 block mb-1">To'lov turi</label>
        <select name="pay_type" class="form-input">
            <option value="">Barcha turlar</option>
            <option value="full"        {{ request('pay_type') === 'full'        ? 'selected' : '' }}>100% to'liq</option>
            <option value="installment" {{ request('pay_type') === 'installment' ? 'selected' : '' }}>Bo'lib to'lash</option>
        </select>
    </div>
    <div class="w-28">
        <label class="text-xs text-gray-500 block mb-1">Yil</label>
        <select name="year" class="form-input">
            <option value="">Barcha yillar</option>
            @foreach($years as $y)
            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}-yil</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn-primary">
        <i class="fa-solid fa-magnifying-glass"></i> Qidirish
    </button>
    @if(request()->hasAny(['search','block_id','status','pay_type','year']))
    <a href="{{ route('contracts.index') }}" class="btn-secondary">
        <i class="fa-solid fa-xmark"></i> Tozalash
    </a>
    @endif
</form>

{{-- Jadval --}}
<div class="card overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase border-b border-gray-100">
            <tr>
                <th class="px-4 py-3 text-left font-semibold">Shartnoma №</th>
                <th class="px-4 py-3 text-left font-semibold">Mijoz</th>
                <th class="px-4 py-3 text-left font-semibold">Xonadon</th>
                <th class="px-4 py-3 text-left font-semibold">To'lov turi</th>
                <th class="px-4 py-3 text-right font-semibold">Narx</th>
                <th class="px-4 py-3 text-right font-semibold">Qarz</th>
                <th class="px-4 py-3 text-center font-semibold">Jarayon</th>
                <th class="px-4 py-3 text-center font-semibold">Holat</th>
                <th class="px-4 py-3 text-center font-semibold">Sana</th>
                <th class="px-4 py-3 w-16"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($contracts as $c)
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
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3">
                    <a href="{{ route('contracts.show', $c) }}"
                       class="font-mono font-bold text-blue-700 hover:underline text-xs">
                        {{ $c->contract_number }}
                    </a>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $c->manager->name }}</p>
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 bg-blue-50 rounded-full flex items-center justify-center
                                    text-xs font-bold text-blue-700 flex-shrink-0">
                            {{ $c->client->initials }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold truncate">{{ $c->client->full_name }}</p>
                            <p class="text-xs text-gray-400">{{ $c->client->phone }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <p class="text-xs font-semibold">
                        {{ $c->apartment->block->name }} · {{ $c->apartment->number }}-xon
                    </p>
                    <p class="text-xs text-gray-400">
                        {{ $c->apartment->floor }}-qavat · {{ $c->apartment->rooms }}x
                    </p>
                </td>
                <td class="px-4 py-3">
                    <p class="text-xs font-semibold">{{ $c->payment_type_badge }}</p>
                    @if($c->payment_type === 'installment' && $c->monthly_payment > 0)
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ number_format($c->monthly_payment) }} so'm/oy
                    </p>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <span class="text-xs font-semibold">{{ number_format($c->final_price) }} so'm</span>
                </td>
                <td class="px-4 py-3 text-right">
                    <span class="text-xs font-semibold
                                 {{ $c->debt_amount > 0 ? 'text-red-600' : 'text-gray-400' }}">
                        {{ number_format($c->debt_amount) }} so'm
                    </span>
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-1.5">
                        <div class="flex-1 bg-gray-100 rounded-full h-1.5 min-w-[40px]">
                            <div class="h-1.5 rounded-full
                                        {{ $prog >= 100 ? 'bg-emerald-500' : 'bg-blue-500' }}"
                                 style="width:{{ $prog }}%"></div>
                        </div>
                        <span class="text-xs text-gray-500 w-8 text-right">{{ $prog }}%</span>
                    </div>
                    @if($c->overdue_count > 0)
                    <p class="text-xs text-red-500 mt-0.5">
                        <i class="fa-solid fa-clock fa-xs"></i>
                        {{ $c->overdue_count }} ta kechikkan
                    </p>
                    @endif
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $sCls }}">
                        {{ $c->status_label }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center text-xs text-gray-400">
                    {{ $c->signed_date?->format('d.m.Y') ?? '—' }}
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('contracts.show', $c) }}"
                       class="text-xs text-blue-600 hover:underline">Ko'rish →</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="px-4 py-16 text-center text-gray-400">
                    <i class="fa-solid fa-folder-open text-4xl block mb-3 text-gray-200"></i>
                    Shartnoma topilmadi
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($contracts->hasPages())
<div class="flex items-center justify-between text-sm text-gray-500">
    <span>Jami {{ $contracts->total() }} ta · {{ $contracts->currentPage() }}-sahifa</span>
    {{ $contracts->links() }}
</div>
@endif

</div>
@endsection
