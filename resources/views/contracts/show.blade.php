@extends('layouts.app')
@section('title', 'Shartnoma ' . $contract->contract_number)
@section('heading', 'Shartnoma №' . $contract->contract_number)
@section('subheading', $contract->client->full_name . ' · ' . $contract->apartment->full_name)

@section('header-actions')
<div class="flex items-center gap-2">
    @if($contract->status === 'draft')
    <button onclick="activateContract()" class="btn-primary">
        <i class="fa-solid fa-check"></i> Faollashtirish
    </button>
    @endif
    @if($contract->status === 'active')
    <button onclick="openPaymentModal()"
            class="btn-primary bg-blue-600 hover:bg-blue-700 border-blue-700">
        <i class="fa-solid fa-money-bill-wave"></i> To'lov qabul qilish
    </button>
    @endif
    <a href="{{ route('contracts.print', $contract) }}" target="_blank" class="btn-secondary">
        <i class="fa-solid fa-print"></i> PDF
    </a>
    @if(in_array($contract->status, ['draft', 'active']) && auth()->user()->isAdmin())
    <button onclick="cancelContract()" class="btn-danger">
        <i class="fa-solid fa-ban"></i> Bekor qilish
    </button>
    @endif
</div>
@endsection

@section('content')
<div class="grid grid-cols-3 gap-5">

{{-- ── ASOSIY QISM (2/3) ──────────────────────────────────────── --}}
<div class="col-span-2 space-y-5">

    {{-- Status banner --}}
    @php
    $bannerMap = [
        'draft'     => ['bg-amber-50',  'border-amber-200',  'text-amber-800',   'fa-pen-to-square',       'Loyiha — hali imzolanmagan'],
        'active'    => ['bg-blue-50',   'border-blue-200',   'text-blue-800',    'fa-circle-check',        'Faol shartnoma'],
        'completed' => ['bg-emerald-50','border-emerald-200','text-emerald-800', 'fa-trophy',              "To'liq to'langan — yakunlangan"],
        'cancelled' => ['bg-red-50',    'border-red-200',    'text-red-800',     'fa-ban',                 'Bekor qilingan'],
        'disputed'  => ['bg-red-50',    'border-red-200',    'text-red-800',     'fa-triangle-exclamation','Muammo bor'],
    ];
    [$bg, $bc, $tc, $ico, $msg] = $bannerMap[$contract->status] ?? $bannerMap['draft'];
    @endphp
    <div class="{{ $bg }} border {{ $bc }} {{ $tc }} rounded-2xl px-5 py-3.5 flex items-center gap-3">
        <i class="fa-solid fa-{{ $ico }} text-base"></i>
        <span class="font-semibold text-sm">{{ $msg }}</span>
        @if($contract->cancel_reason)
        <span class="ml-2 text-xs opacity-75">— {{ $contract->cancel_reason }}</span>
        @endif
        @if($contract->signed_date)
        <span class="ml-auto text-xs opacity-60">
            Imzolangan: {{ $contract->signed_date->format('d.m.Y') }}
        </span>
        @endif
    </div>

    {{-- To'lov jarayoni --}}
    <div class="card p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-sm">To'lov holati</h3>
            <span class="text-sm font-bold {{ $contract->progress_percent >= 100 ? 'text-emerald-600' : 'text-gray-700' }}">
                {{ $contract->progress_percent }}%
            </span>
        </div>
        <div class="bg-gray-100 rounded-full h-2.5 mb-4">
            <div class="h-2.5 rounded-full transition-all
                        {{ $contract->progress_percent >= 100 ? 'bg-emerald-500' : 'bg-blue-500' }}"
                 style="width: {{ $contract->progress_percent }}%"></div>
        </div>
        <div class="grid grid-cols-4 gap-3 text-center text-sm">
            <div class="bg-gray-50 rounded-xl p-3">
                <p class="text-xs text-gray-500 mb-1">Yakuniy narx</p>
                <p class="font-bold">{{ number_format($contract->final_price) }} so'm</p>
            </div>
            <div class="bg-emerald-50 rounded-xl p-3">
                <p class="text-xs text-gray-500 mb-1">To'langan</p>
                <p class="font-bold text-emerald-700">{{ number_format($contract->paid_amount) }} so'm</p>
            </div>
            <div class="{{ $contract->debt_amount > 0 ? 'bg-red-50' : 'bg-gray-50' }} rounded-xl p-3">
                <p class="text-xs text-gray-500 mb-1">Qarz</p>
                <p class="font-bold {{ $contract->debt_amount > 0 ? 'text-red-600' : 'text-gray-400' }}">
                    {{ number_format($contract->debt_amount) }} so'm
                </p>
            </div>
            <div class="{{ $contract->overdue_count > 0 ? 'bg-red-50' : 'bg-gray-50' }} rounded-xl p-3">
                <p class="text-xs text-gray-500 mb-1">Muddati o'tgan</p>
                <p class="font-bold {{ $contract->overdue_count > 0 ? 'text-red-600' : 'text-gray-400' }}">
                    {{ $contract->overdue_count }} ta
                </p>
            </div>
        </div>
    </div>

    {{-- Shartnoma tafsilotlari --}}
    <div class="card p-5">
        <h3 class="font-semibold text-sm mb-4">Shartnoma tafsilotlari</h3>
        <div class="grid grid-cols-2 gap-x-10 gap-y-2.5 text-sm">
            @php
            $rows = [
                ['Shartnoma №',   '<span class="font-mono font-bold text-blue-700">'.$contract->contract_number.'</span>'],
                ["To'lov turi",   e($contract->payment_type_label)],
                ['Umumiy narx',   number_format($contract->total_price)." so'm"],
            ];
            if ($contract->discount_amount > 0) {
                $rows[] = ['Chegirma', '<span class="text-red-600">-'.number_format($contract->discount_amount)." so'm</span>"];
            }
            $rows[] = ['Yakuniy narx', '<span class="font-semibold text-emerald-700">'.number_format($contract->final_price)." so'm</span>"];
            if ($contract->payment_type === 'installment') {
                $rows[] = ["Boshlang'ich", number_format($contract->initial_payment)." so'm (".$contract->initial_percent.'%)'];
                $rows[] = ['Oylik to\'lov', '<span class="font-semibold text-blue-700">'.number_format($contract->monthly_payment)." so'm</span>"];
                $rows[] = ['Muddat', $contract->installment_start_date?->format('M Y').' — '.$contract->installment_end_date?->format('M Y')];
            }
            $rows[] = ['Menejer', e($contract->manager->name)];
            if ($contract->expected_handover_date) {
                $rows[] = ['Topshirish (reja)', $contract->expected_handover_date->format('d.m.Y')];
            }
            @endphp
            @foreach($rows as [$label, $value])
            <div class="flex justify-between py-1 border-b border-gray-50">
                <span class="text-gray-500">{{ $label }}</span>
                <span>{!! $value !!}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- To'lov jadvali --}}
    @if($contract->payment_type === 'installment' && $contract->paymentSchedules->count())
    <div class="card overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-sm">To'lov jadvali</h3>
            <span class="text-sm text-gray-400">
                {{ $contract->paymentSchedules->where('status','paid')->count() }} /
                {{ $contract->paymentSchedules->count() }} to'landi
            </span>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-2.5 text-left w-10">№</th>
                    <th class="px-4 py-2.5 text-left">Muddat</th>
                    <th class="px-4 py-2.5 text-right">Summa</th>
                    <th class="px-4 py-2.5 text-right">To'landi</th>
                    <th class="px-4 py-2.5 text-center">Holat</th>
                    @if($contract->status === 'active')
                    <th class="px-4 py-2.5 w-20"></th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($contract->paymentSchedules as $sched)
                @php
                $isOverdue = $sched->isOverdue();
                $badgeCls = match(true) {
                    $sched->status === 'paid'    => 'bg-emerald-100 text-emerald-700',
                    $sched->status === 'partial' => 'bg-blue-100 text-blue-700',
                    $isOverdue                   => 'bg-red-100 text-red-700',
                    default                      => 'bg-gray-100 text-gray-600',
                };
                @endphp
                <tr class="{{ $isOverdue ? 'bg-red-50/40' : 'hover:bg-gray-50' }} transition">
                    <td class="px-4 py-3 text-gray-500 font-medium">{{ $sched->payment_number }}</td>
                    <td class="px-4 py-3 {{ $isOverdue ? 'text-red-700 font-semibold' : '' }}">
                        {{ $sched->due_date->format('d.m.Y') }}
                        @if($isOverdue)
                        <span class="text-xs text-red-500 ml-1">({{ $sched->days_overdue }} kun)</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">{{ number_format($sched->amount) }} so'm</td>
                    <td class="px-4 py-3 text-right text-emerald-700">
                        @if($sched->paid_amount > 0) {{ number_format($sched->paid_amount) }} so'm @else — @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $badgeCls }}">
                            {{ $sched->status_label }}
                        </span>
                    </td>
                    @if($contract->status === 'active')
                    <td class="px-4 py-3 text-right">
                        @if(in_array($sched->status, ['pending','partial','overdue']))
                        <button onclick="openPaymentModal({{ $sched->id }}, {{ $sched->remaining }})"
                                class="text-xs text-blue-600 hover:underline whitespace-nowrap">
                            To'lov →
                        </button>
                        @endif
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- To'lovlar tarixi --}}
    @if($contract->payments->count())
    <div class="card overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100">
            <h3 class="font-semibold text-sm">To'lovlar tarixi</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-2.5 text-left">Kvitansiya №</th>
                    <th class="px-4 py-2.5 text-left">Sana</th>
                    <th class="px-4 py-2.5 text-left">Tur</th>
                    <th class="px-4 py-2.5 text-right">Summa</th>
                    <th class="px-4 py-2.5 text-left">Usul</th>
                    <th class="px-4 py-2.5 text-left">Kim qabul qildi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($contract->payments as $pay)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-mono text-xs text-blue-700 font-semibold">
                        {{ $pay->receipt_number }}
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">
                        {{ $pay->payment_date->format('d.m.Y') }}
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $pay->type_label }}</td>
                    <td class="px-4 py-3 text-right font-semibold
                               {{ $pay->type === 'refund' ? 'text-red-600' : 'text-emerald-700' }}">
                        {{ $pay->type === 'refund' ? '-' : '+' }}{{ $pay->formatted_amount }}
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600">{{ $pay->method_label }}</td>
                    <td class="px-4 py-3 text-xs text-gray-600">{{ $pay->receivedBy->name }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- ── O'NG PANEL (1/3) ────────────────────────────────────────── --}}
<div class="space-y-4">

    {{-- Xonadon --}}
    <div class="card p-4">
        <p class="text-xs text-gray-400 font-semibold mb-3 flex items-center gap-1.5">
            <i class="fa-solid fa-home text-gray-300"></i> Xonadon
        </p>
        <a href="{{ route('apartments.block', $contract->apartment->block) }}"
           class="font-semibold hover:text-emerald-700 transition text-sm">
            {{ $contract->apartment->block->name }} · {{ $contract->apartment->number }}-xonadon
        </a>
        <div class="grid grid-cols-2 gap-1.5 mt-2.5 text-xs text-gray-500">
            <span><i class="fa-solid fa-stairs fa-xs mr-1 text-gray-300"></i>{{ $contract->apartment->floor }}-qavat</span>
            <span><i class="fa-solid fa-border-all fa-xs mr-1 text-gray-300"></i>{{ $contract->apartment->rooms }} xona</span>
            <span><i class="fa-solid fa-vector-square fa-xs mr-1 text-gray-300"></i>{{ $contract->apartment->area_total }} m²</span>
            <span><i class="fa-solid fa-paintbrush fa-xs mr-1 text-gray-300"></i>{{ $contract->apartment->renovation_label }}</span>
        </div>
    </div>

    {{-- Mijoz --}}
    <div class="card p-4">
        <p class="text-xs text-gray-400 font-semibold mb-3 flex items-center gap-1.5">
            <i class="fa-solid fa-user text-gray-300"></i> Mijoz
        </p>
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center
                        text-xs font-bold text-blue-700 flex-shrink-0">
                {{ $contract->client->initials }}
            </div>
            <a href="{{ route('clients.show', $contract->client) }}"
               class="font-semibold text-sm hover:text-emerald-700 transition">
                {{ $contract->client->full_name }}
            </a>
        </div>
        <div class="space-y-1.5 text-xs text-gray-500">
            <p><i class="fa-solid fa-phone fa-xs mr-1.5 text-gray-300"></i>{{ $contract->client->phone }}</p>
            @if($contract->client->passport_series)
            <p><i class="fa-solid fa-id-card fa-xs mr-1.5 text-gray-300"></i>{{ $contract->client->passport_series }}</p>
            @endif
            @if($contract->client->pinfl)
            <p><i class="fa-solid fa-fingerprint fa-xs mr-1.5 text-gray-300"></i>PINFL: {{ $contract->client->pinfl }}</p>
            @endif
            @if($contract->client->address)
            <p class="text-gray-400 mt-1">{{ $contract->client->address }}</p>
            @endif
        </div>
    </div>

    {{-- Xonadon egasi --}}
    @if($contract->apartment->owner)
    <div class="card p-4">
        <p class="text-xs text-gray-400 font-semibold mb-3 flex items-center gap-1.5">
            <i class="fa-solid fa-user-tie text-gray-300"></i> Xonadon egasi
        </p>
        <p class="text-sm font-semibold">{{ $contract->apartment->owner->full_name }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ $contract->apartment->owner->phone }}</p>
        @if($contract->apartment->owner->passport_series)
        <p class="text-xs text-gray-400">{{ $contract->apartment->owner->passport_series }}</p>
        @endif
    </div>
    @endif

    {{-- Faollik tarixi --}}
    @if($contract->activityLogs->count())
    <div class="card overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold">Faollik tarixi</h3>
        </div>
        <div class="divide-y divide-gray-50 max-h-60 overflow-y-auto">
            @foreach($contract->activityLogs->take(15) as $log)
            <div class="px-4 py-2.5">
                <p class="text-xs text-gray-700">{{ $log->description }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $log->user?->name ?? 'Tizim' }} ·
                    {{ $log->created_at->diffForHumans() }}
                </p>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

</div>

{{-- To'lov modali shabloni --}}
<template id="payment-modal-tpl">
    <div class="p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-lg font-bold">To'lov qabul qilish</h3>
                <p class="text-sm text-gray-500 font-mono mt-0.5">{{ $contract->contract_number }}</p>
            </div>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="text-sm font-semibold text-gray-700 block mb-1.5">Summa (so'm) *</label>
                <input id="pay-amount" type="number" min="0.01" step="0.01" class="form-input" placeholder="0.00">
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700 block mb-1.5">To'lov sanasi *</label>
                <input id="pay-date" type="date" class="form-input" value="{{ now()->toDateString() }}">
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700 block mb-1.5">To'lov turi</label>
                <select id="pay-type" class="form-input">
                    @foreach(\App\Models\Payment::TYPE_LABELS as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700 block mb-1.5">To'lov usuli</label>
                <select id="pay-method" class="form-input">
                    @foreach(\App\Models\Payment::METHOD_LABELS as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700 block mb-1.5">Jadval to'lovi</label>
                <select id="pay-sched" class="form-input">
                    <option value="">— Ixtiyoriy —</option>
                    @foreach($contract->paymentSchedules->whereIn('status',['pending','partial','overdue']) as $s)
                    <option value="{{ $s->id }}">
                        {{ $s->payment_number }}-to'lov · {{ $s->due_date->format('d.m.Y') }} ·
                        {{ number_format($s->remaining) }} so'm
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700 block mb-1.5">Bank hujjat №</label>
                <input id="pay-ref" type="text" class="form-input" placeholder="(ixtiyoriy)">
            </div>
            <div class="col-span-2">
                <label class="text-sm font-semibold text-gray-700 block mb-1.5">Izoh</label>
                <textarea id="pay-notes" class="form-input resize-none" rows="2"
                          placeholder="Qo'shimcha ma'lumot..."></textarea>
            </div>
            <div class="col-span-2">
                <button onclick="submitPayment()" id="pay-submit-btn"
                        class="w-full bg-blue-600 text-white py-3 rounded-2xl font-semibold text-sm
                               hover:bg-blue-700 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-check"></i> To'lovni tasdiqlash
                </button>
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script>
const contractId = {{ $contract->id }};

function openPaymentModal(schedId = null, amount = null) {
    const tpl = document.getElementById('payment-modal-tpl');
    openModal(tpl.innerHTML);
    if (amount) {
        setTimeout(() => {
            const el = document.getElementById('pay-amount');
            if (el) el.value = amount;
        }, 30);
    }
    if (schedId) {
        setTimeout(() => {
            const sel = document.getElementById('pay-sched');
            if (sel) sel.value = schedId;
            const tp = document.getElementById('pay-type');
            if (tp) tp.value = 'installment';
        }, 30);
    }
}

async function submitPayment() {
    const btn    = document.getElementById('pay-submit-btn');
    const amount = parseFloat(document.getElementById('pay-amount')?.value);

    if (!amount || amount <= 0) {
        showToast("Summa kiritilmadi yoki noto'g'ri", 'error');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin mr-2"></i>Saqlanmoqda...';

    const data = {
        amount,
        payment_date:        document.getElementById('pay-date')?.value,
        type:                document.getElementById('pay-type')?.value,
        payment_method:      document.getElementById('pay-method')?.value,
        bank_reference:      document.getElementById('pay-ref')?.value || null,
        payment_schedule_id: document.getElementById('pay-sched')?.value || null,
        notes:               document.getElementById('pay-notes')?.value || null,
    };

    const d = await apiPost(`/contracts/${contractId}/payments`, data);

    if (d.success) {
        showToast(d.message, 'success');
        closeModal();
        setTimeout(() => location.reload(), 700);
    } else {
        const errMsg = d.errors
            ? Object.values(d.errors).flat().join('\n')
            : (d.message ?? "Xatolik yuz berdi");
        showToast(errMsg, 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-check mr-2"></i>To\'lovni tasdiqlash';
    }
}

async function activateContract() {
    if (!confirm("Shartnomani faollashtirishni tasdiqlaysizmi?\n\nXonadon «Sotilgan» deb belgilanadi.")) return;
    const d = await apiPost(`/contracts/${contractId}/activate`);
    showToast(d.message || (d.success ? 'Faollashtirildi' : 'Xato'), d.success ? 'success' : 'error');
    if (d.success) setTimeout(() => location.reload(), 700);
}

async function cancelContract() {
    const reason = prompt('Bekor qilish sababini kiriting (kamida 5 belgi):');
    if (!reason || reason.trim().length < 5) {
        if (reason !== null) showToast("Sabab kamida 5 ta belgi bo'lishi kerak", 'warning');
        return;
    }
    const d = await apiPost(`/contracts/${contractId}/cancel`, { reason });
    showToast(d.success ? 'Shartnoma bekor qilindi' : (d.message || 'Xato'), d.success ? 'info' : 'error');
    if (d.success) setTimeout(() => location.reload(), 700);
}
</script>
@endpush
