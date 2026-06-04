@extends('layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@php
$days = ['Yakshanba','Dushanba','Seshanba','Chorshanba','Payshanba','Juma','Shanba'];
$todayName = $days[now()->dayOfWeek];
@endphp
@section('subheading', now()->format('d.m.Y') . ', ' . $todayName . ' · Umumiy holat')

@section('header-actions')
<a href="{{ route('contracts.create') }}" class="btn-primary">
    <i class="fa-solid fa-plus"></i> Yangi shartnoma
</a>
@endsection

@section('content')
<div style="display:flex; flex-direction:column; gap:20px;">

{{-- ── Statistika kartochkalari ─────────────────────── --}}
@php
$cards = [
    [
        'label' => 'Faol shartnomalar',
        'val'   => $stats['active_contracts'],
        'icon'  => 'fa-file-contract',
        'color' => '#2563eb',
        'bg'    => '#eff6ff',
        'border'=> '#3b82f6',
    ],
    [
        'label' => 'Jami tushum',
        'val'   => number_format($stats['total_received'], 0, '.', ' ') . " so'm",
        'icon'  => 'fa-money-bill-wave',
        'color' => '#059669',
        'bg'    => '#ecfdf5',
        'border'=> '#10b981',
    ],
    [
        'label' => 'Jami qarz',
        'val'   => number_format($stats['total_debt'], 0, '.', ' ') . " so'm",
        'icon'  => 'fa-circle-exclamation',
        'color' => '#d97706',
        'bg'    => '#fffbeb',
        'border'=> '#f59e0b',
    ],
    [
        'label' => "Muddati o'tgan",
        'val'   => $stats['overdue_schedules'],
        'icon'  => 'fa-clock',
        'color' => '#dc2626',
        'bg'    => '#fef2f2',
        'border'=> '#ef4444',
    ],
    [
        'label' => 'Jami shartnomalar',
        'val'   => $stats['total_contracts'],
        'icon'  => 'fa-list-check',
        'color' => '#6b7280',
        'bg'    => '#f9fafb',
        'border'=> '#d1d5db',
    ],
];
@endphp

<div style="display:grid; grid-template-columns:repeat(5,1fr); gap:14px;">
@foreach($cards as $c)
<div class="card" style="padding:16px 18px; border-left:4px solid {{ $c['border'] }};">
    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:8px;">
        <div style="min-width:0;">
            <div style="font-size:11.5px; color:#6b7280; margin-bottom:6px; font-weight:500;">{{ $c['label'] }}</div>
            <div style="font-size:{{ strlen((string)$c['val']) > 12 ? '15' : '22' }}px;
                        font-weight:700; color:{{ $c['color'] }}; line-height:1.2; word-break:break-all;">
                {{ $c['val'] }}
            </div>
        </div>
        <div style="width:38px; height:38px; background:{{ $c['bg'] }}; border-radius:10px;
                    display:flex; align-items:center; justify-content:center; flex-shrink:0;
                    border:1px solid {{ $c['border'] }}20;">
            <i class="fa-solid {{ $c['icon'] }}" style="font-size:15px; color:{{ $c['color'] }};"></i>
        </div>
    </div>
</div>
@endforeach
</div>

{{-- ── Bloklar ──────────────────────────────────────── --}}
<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:14px;">
@foreach($blocks as $blk)
@php
    $total   = $blk->apartments_count ?: 1;
    $soldPct = round(($blk->sold_count / $total) * 100);
@endphp
<a href="{{ route('apartments.block', $blk) }}" class="card"
   style="padding:16px 18px; display:block; text-decoration:none; color:inherit;
          transition:box-shadow .15s, border-color .15s;"
   onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,.1)'"
   onmouseout="this.style.boxShadow=''">

    <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
        <span style="width:12px; height:12px; border-radius:50%; background:{{ $blk->color }};
                     flex-shrink:0; display:inline-block;"></span>
        <span style="font-weight:700; font-size:14px; color:#111827; flex:1;">{{ $blk->name }}</span>
        <span style="font-size:11.5px; color:#9ca3af; background:#f3f4f6;
                     padding:2px 8px; border-radius:6px;">{{ $blk->total_floors }}-qavat</span>
    </div>

    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:6px; margin-bottom:14px;">
        @foreach([
            ['free_count',     "Bo'sh",   '#ecfdf5','#059669'],
            ['reserved_count', 'Band',    '#fffbeb','#d97706'],
            ['sold_count',     'Sotilgan','#fef2f2','#dc2626'],
        ] as [$k,$l,$bg,$clr])
        <div style="background:{{ $bg }}; border-radius:8px; padding:8px 4px; text-align:center;">
            <div style="font-size:17px; font-weight:700; color:{{ $clr }}; line-height:1;">{{ $blk->{$k} }}</div>
            <div style="font-size:10px; color:{{ $clr }}; margin-top:2px; opacity:.8;">{{ $l }}</div>
        </div>
        @endforeach
    </div>

    <div>
        <div style="display:flex; justify-content:space-between; font-size:11.5px; color:#9ca3af; margin-bottom:5px;">
            <span>Sotuv: <b style="color:#374151;">{{ $soldPct }}%</b></span>
            <span>{{ $blk->apartments_count }} xonadon</span>
        </div>
        <div style="background:#f3f4f6; border-radius:99px; height:6px; overflow:hidden;">
            <div style="height:6px; border-radius:99px; background:{{ $blk->color }};
                        width:{{ $soldPct }}%; transition:width .6s ease;"></div>
        </div>
    </div>
</a>
@endforeach
</div>

{{-- ── So'nggi to'lovlar + Muddati o'tganlar ──────── --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">

    {{-- So'nggi to'lovlar --}}
    <div class="card" style="overflow:hidden;">
        <div style="padding:14px 20px; border-bottom:1px solid #f3f4f6;
                    display:flex; align-items:center; justify-content:space-between;">
            <div style="font-weight:600; font-size:13.5px; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-receipt" style="color:#059669;"></i>
                So'nggi to'lovlar
            </div>
            <a href="{{ route('payments.index') }}"
               style="font-size:12px; color:#059669; text-decoration:none; font-weight:500;"
               onmouseover="this.style.textDecoration='underline'"
               onmouseout="this.style.textDecoration='none'">Barchasi →</a>
        </div>
        @forelse($recentPayments as $pay)
        <div class="tbl-row">
            <div style="width:34px; height:34px; background:#ecfdf5; border-radius:50%;
                        display:flex; align-items:center; justify-content:center;
                        font-size:12px; font-weight:700; color:#065f46; flex-shrink:0;">
                {{ $pay->contract->client->initials ?? '?' }}
            </div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:13.5px; font-weight:500; color:#111827;
                            overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    {{ $pay->contract->client->full_name ?? '—' }}
                </div>
                <div style="font-size:11.5px; color:#9ca3af; margin-top:1px;">
                    {{ $pay->receipt_number }} · {{ $pay->contract->apartment->block->name ?? '' }}
                </div>
            </div>
            <div style="text-align:right; flex-shrink:0;">
                <div style="font-size:13px; font-weight:700; color:#059669;">
                    {{ number_format((float)$pay->amount, 0, '.', ' ') }} so'm
                </div>
                <div style="font-size:11px; color:#9ca3af;">{{ $pay->payment_date->format('d.m.Y') }}</div>
            </div>
        </div>
        @empty
        <div style="padding:40px 20px; text-align:center; color:#9ca3af; font-size:13.5px;">
            <i class="fa-solid fa-inbox" style="font-size:28px; display:block; margin-bottom:8px; color:#d1d5db;"></i>
            Hali to'lov yo'q
        </div>
        @endforelse
    </div>

    {{-- Muddati o'tgan to'lovlar --}}
    <div class="card" style="overflow:hidden;">
        <div style="padding:14px 20px; border-bottom:1px solid #f3f4f6;
                    display:flex; align-items:center; justify-content:space-between;">
            <div style="font-weight:600; font-size:13.5px; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-triangle-exclamation" style="color:#dc2626;"></i>
                Muddati o'tgan
                @if($stats['overdue_schedules'] > 0)
                <span style="background:#fee2e2; color:#b91c1c; font-size:11px; font-weight:700;
                             padding:1px 8px; border-radius:99px;">{{ $stats['overdue_schedules'] }}</span>
                @endif
            </div>
            <a href="{{ route('payments.overdue') }}"
               style="font-size:12px; color:#dc2626; text-decoration:none; font-weight:500;"
               onmouseover="this.style.textDecoration='underline'"
               onmouseout="this.style.textDecoration='none'">Barchasi →</a>
        </div>
        @forelse($overdueSchedules as $sched)
        <div class="tbl-row">
            <div style="width:34px; height:34px; background:#fef2f2; border-radius:50%;
                        display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="fa-solid fa-clock" style="color:#f87171; font-size:13px;"></i>
            </div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:13.5px; font-weight:500; color:#111827;
                            overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    {{ $sched->contract->client->full_name ?? '—' }}
                </div>
                <div style="font-size:11.5px; color:#9ca3af; margin-top:1px;">
                    {{ $sched->contract->contract_number }} · {{ $sched->payment_number }}-to'lov
                </div>
            </div>
            <div style="text-align:right; flex-shrink:0;">
                <div style="font-size:13px; font-weight:700; color:#dc2626;">
                    {{ number_format((float)$sched->amount, 0, '.', ' ') }} so'm
                </div>
                <div style="font-size:11px; color:#f87171;">
                    {{ $sched->due_date->format('d.m.Y') }}
                    ({{ $sched->days_overdue }} kun)
                </div>
            </div>
        </div>
        @empty
        <div style="padding:40px 20px; text-align:center; color:#9ca3af; font-size:13.5px;">
            <i class="fa-solid fa-circle-check" style="font-size:32px; display:block; margin-bottom:8px; color:#6ee7b7;"></i>
            Barcha to'lovlar o'z vaqtida!
        </div>
        @endforelse
    </div>

</div>
</div>
@endsection
