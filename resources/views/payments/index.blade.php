@extends('layouts.app')
@section('title', "To'lovlar")
@section('heading', "To'lovlar tarixi")
@section('subheading',
    number_format($payments->total()) . " ta to'lov · Jami: $" . number_format($total))

@section('header-actions')
<a href="{{ route('payments.overdue') }}" class="btn-secondary
   {{ \App\Models\PaymentSchedule::where('status','overdue')->exists() ? 'border-red-200 text-red-600' : '' }}">
    <i class="fa-solid fa-clock"></i> Muddati o'tganlar
</a>
@endsection

@section('content')
<div class="space-y-4">

{{-- Filtrlar --}}
<form method="GET" class="card p-4 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-40">
        <label class="text-xs text-gray-500 block mb-1">Qidiruv</label>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Kvitansiya №, mijoz ismi..." class="form-input">
    </div>
    <div class="w-36">
        <label class="text-xs text-gray-500 block mb-1">To'lov usuli</label>
        <select name="method" class="form-input">
            <option value="">Barcha usullar</option>
            @foreach(\App\Models\Payment::METHOD_LABELS as $k => $v)
            <option value="{{ $k }}" {{ request('method') === $k ? 'selected' : '' }}>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div class="w-36">
        <label class="text-xs text-gray-500 block mb-1">To'lov turi</label>
        <select name="type" class="form-input">
            <option value="">Barcha turlar</option>
            @foreach(\App\Models\Payment::TYPE_LABELS as $k => $v)
            <option value="{{ $k }}" {{ request('type') === $k ? 'selected' : '' }}>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div class="w-36">
        <label class="text-xs text-gray-500 block mb-1">Dan</label>
        <input type="date" name="from" value="{{ request('from') }}" class="form-input">
    </div>
    <div class="w-36">
        <label class="text-xs text-gray-500 block mb-1">Gacha</label>
        <input type="date" name="to" value="{{ request('to') }}" class="form-input">
    </div>
    <button type="submit" class="btn-primary">
        <i class="fa-solid fa-magnifying-glass"></i> Qidirish
    </button>
    @if(request()->hasAny(['search','method','type','from','to']))
    <a href="{{ route('payments.index') }}" class="btn-secondary">
        <i class="fa-solid fa-xmark"></i> Tozalash
    </a>
    @endif
</form>

{{-- Usul bo'yicha jami --}}
<div class="grid grid-cols-4 gap-3">
    @foreach([
        ['cash',  'Naqd pul',      'fa-money-bill'],
        ['bank',  "Bank o'tkazma", 'fa-building-columns'],
        ['card',  'Plastik karta', 'fa-credit-card'],
        ['online',"Onlayn to'lov", 'fa-mobile-screen'],
    ] as [$key, $label, $icon])
    @php
    $amt = \App\Models\Payment::where('payment_method', $key)
        ->when(request('from'), fn($q) => $q->where('payment_date','>=',request('from')))
        ->when(request('to'),   fn($q) => $q->where('payment_date','<=',request('to')))
        ->where('type','!=','refund')->sum('amount');
    @endphp
    <div class="card p-4 flex items-center gap-3">
        <div class="w-9 h-9 bg-emerald-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa-solid {{ $icon }} text-emerald-600 text-sm"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">{{ $label }}</p>
            <p class="font-bold text-sm">{{ number_format($amt) }} so'm</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Jadval --}}
<div class="card overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase border-b border-gray-100">
            <tr>
                <th class="px-4 py-3 text-left font-semibold">Kvitansiya №</th>
                <th class="px-4 py-3 text-left font-semibold">Mijoz</th>
                <th class="px-4 py-3 text-left font-semibold">Shartnoma</th>
                <th class="px-4 py-3 text-left font-semibold">To'lov turi</th>
                <th class="px-4 py-3 text-left font-semibold">Usul</th>
                <th class="px-4 py-3 text-right font-semibold">Summa</th>
                <th class="px-4 py-3 text-center font-semibold">Sana</th>
                <th class="px-4 py-3 text-left font-semibold">Kim qabul qildi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($payments as $pay)
            @php
            $mCls = match($pay->payment_method) {
                'cash'   => 'bg-emerald-100 text-emerald-700',
                'bank'   => 'bg-blue-100 text-blue-700',
                'card'   => 'bg-purple-100 text-purple-700',
                'online' => 'bg-amber-100 text-amber-700',
                default  => 'bg-gray-100 text-gray-600',
            };
            @endphp
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3">
                    <span class="font-mono text-xs text-blue-700 font-bold">
                        {{ $pay->receipt_number }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <a href="{{ route('clients.show', $pay->contract->client) }}"
                       class="text-xs font-semibold hover:text-emerald-700 transition block">
                        {{ $pay->contract->client->full_name }}
                    </a>
                    <p class="text-xs text-gray-400">{{ $pay->contract->client->phone }}</p>
                </td>
                <td class="px-4 py-3">
                    <a href="{{ route('contracts.show', $pay->contract) }}"
                       class="font-mono text-xs text-blue-600 hover:underline">
                        {{ $pay->contract->contract_number }}
                    </a>
                    <p class="text-xs text-gray-400">
                        {{ $pay->contract->apartment->block->name ?? '' }} ·
                        {{ $pay->contract->apartment->number ?? '' }}-xon
                    </p>
                </td>
                <td class="px-4 py-3 text-xs text-gray-600">{{ $pay->type_label }}</td>
                <td class="px-4 py-3">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $mCls }}">
                        {{ $pay->method_label }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <span class="font-bold text-sm
                                 {{ $pay->type === 'refund' ? 'text-red-600' : 'text-emerald-700' }}">
                        {{ $pay->type === 'refund' ? '-' : '+' }}{{ $pay->formatted_amount }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center text-xs text-gray-500">
                    {{ $pay->payment_date->format('d.m.Y') }}
                </td>
                <td class="px-4 py-3 text-xs text-gray-600">{{ $pay->receivedBy->name }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-16 text-center text-gray-400">
                    <i class="fa-solid fa-receipt text-4xl block mb-3 text-gray-200"></i>
                    To'lov topilmadi
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($payments->hasPages())
<div class="flex justify-between items-center text-sm text-gray-500">
    <span>Jami {{ $payments->total() }} ta</span>
    {{ $payments->links() }}
</div>
@endif

</div>
@endsection
