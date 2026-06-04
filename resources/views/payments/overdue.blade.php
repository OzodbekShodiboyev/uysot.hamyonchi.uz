@extends('layouts.app')
@section('title', "Muddati o'tgan to'lovlar")
@section('heading', "Muddati o'tgan to'lovlar")
@section('subheading', number_format($schedules->total()) . " ta muddati o'tgan to'lov jadvali")

@section('header-actions')
<a href="{{ route('payments.index') }}" class="btn-secondary">
    <i class="fa-solid fa-arrow-left"></i> Barcha to'lovlar
</a>
@endsection

@section('content')
<div class="space-y-4">

@if($schedules->isEmpty())
<div class="card p-20 text-center">
    <i class="fa-solid fa-circle-check text-6xl text-emerald-300 block mb-4"></i>
    <p class="text-xl font-bold text-emerald-700">Barcha to'lovlar o'z vaqtida!</p>
    <p class="text-sm text-gray-500 mt-2">Hozircha muddati o'tgan to'lov jadvali yo'q</p>
</div>
@else

<div class="card overflow-hidden">
    <div class="px-5 py-3.5 border-b border-red-100 bg-red-50/50 flex items-center gap-2.5">
        <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
        <p class="text-sm font-semibold text-red-700">
            {{ $schedules->total() }} ta to'lov jadvali muddati o'tgan
        </p>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase border-b border-gray-100">
            <tr>
                <th class="px-4 py-3 text-left font-semibold">Shartnoma</th>
                <th class="px-4 py-3 text-left font-semibold">Mijoz</th>
                <th class="px-4 py-3 text-left font-semibold">Xonadon</th>
                <th class="px-4 py-3 text-center font-semibold">To'lov №</th>
                <th class="px-4 py-3 text-center font-semibold">Muddat</th>
                <th class="px-4 py-3 text-center font-semibold">Kechikish</th>
                <th class="px-4 py-3 text-right font-semibold">Summa</th>
                <th class="px-4 py-3 text-right font-semibold">Qoldi</th>
                <th class="px-4 py-3 w-20"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($schedules as $s)
            <tr class="hover:bg-red-50/30 transition">
                <td class="px-4 py-3">
                    <a href="{{ route('contracts.show', $s->contract) }}"
                       class="font-mono text-xs font-bold text-blue-700 hover:underline">
                        {{ $s->contract->contract_number }}
                    </a>
                </td>
                <td class="px-4 py-3">
                    <a href="{{ route('clients.show', $s->contract->client) }}"
                       class="text-xs font-semibold hover:text-emerald-700 transition block">
                        {{ $s->contract->client->full_name }}
                    </a>
                    <p class="text-xs text-gray-400">{{ $s->contract->client->phone }}</p>
                </td>
                <td class="px-4 py-3 text-xs text-gray-600">
                    {{ $s->contract->apartment->block->name }} ·
                    {{ $s->contract->apartment->number }}-xon
                </td>
                <td class="px-4 py-3 text-center text-xs text-gray-600">
                    {{ $s->payment_number }} / {{ $s->total_payments }}
                </td>
                <td class="px-4 py-3 text-center text-xs font-bold text-red-700">
                    {{ $s->due_date->format('d.m.Y') }}
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1
                                 bg-red-100 text-red-700 rounded-full text-xs font-bold">
                        <i class="fa-solid fa-clock fa-xs"></i>
                        {{ $s->days_overdue }} kun
                    </span>
                </td>
                <td class="px-4 py-3 text-right text-xs font-semibold">
                    {{ number_format($s->amount) }} so'm
                </td>
                <td class="px-4 py-3 text-right text-xs font-bold text-red-600">
                    {{ number_format($s->remaining) }} so'm
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('contracts.show', $s->contract) }}"
                       class="text-xs text-blue-600 hover:underline whitespace-nowrap">
                        To'lov →
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($schedules->hasPages())
<div class="flex justify-end text-sm text-gray-500">
    {{ $schedules->links() }}
</div>
@endif

@endif
</div>
@endsection
