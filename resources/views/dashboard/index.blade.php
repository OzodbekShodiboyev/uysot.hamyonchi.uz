@extends('layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@php
$days = ['Yakshanba','Dushanba','Seshanba','Chorshanba','Payshanba','Juma','Shanba'];
@endphp
@section('subheading', now()->format('d.m.Y') . ', ' . $days[now()->dayOfWeek])

@section('header-actions')
<a href="{{ route('contracts.create') }}" class="btn-primary">
    <i class="fa-solid fa-plus"></i> Yangi shartnoma
</a>
@endsection

@section('content')
<div style="display:flex; flex-direction:column; gap:20px;">

{{-- ── Stat kartochkalar ───────────────────────────── --}}
<div style="display:grid; grid-template-columns:repeat(5,1fr); gap:14px;">
@php
$statCards = [
    ['label'=>'Faol shartnomalar','val'=>$stats['active_contracts'],
     'icon'=>'fa-file-contract','color'=>'#2563eb','bg'=>'#eff6ff','sub'=>'ta shartnoma'],
    ['label'=>'Jami tushum','val'=>number_format($stats['total_received'],0,'.',' ')." so'm",
     'icon'=>'fa-arrow-trend-up','color'=>'#059669','bg'=>'#ecfdf5','sub'=>'qabul qilingan'],
    ['label'=>'Jami qarz','val'=>number_format($stats['total_debt'],0,'.',' ')." so'm",
     'icon'=>'fa-circle-exclamation','color'=>'#d97706','bg'=>'#fffbeb','sub'=>'to\'lanmagan'],
    ['label'=>"Muddati o'tgan",'val'=>$stats['overdue_schedules'],
     'icon'=>'fa-clock','color'=>'#dc2626','bg'=>'#fef2f2','sub'=>'ta jadval'],
    ['label'=>'Jami shartnomalar','val'=>$stats['total_contracts'],
     'icon'=>'fa-list-check','color'=>'#6366f1','bg'=>'#f0f0ff','sub'=>'ta jami'],
];
@endphp
@foreach($statCards as $c)
<div class="card" style="padding:18px 20px; position:relative; overflow:hidden;">
    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:8px;">
        <div style="flex:1; min-width:0;">
            <p style="font-size:11.5px; color:#64748b; margin:0 0 8px; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                {{ $c['label'] }}
            </p>
            <p style="font-size:{{ strlen((string)$c['val']) > 10 ? '14' : '20' }}px;
                      font-weight:700; color:#0f172a; margin:0; line-height:1.2;
                      word-break:break-all;">
                {{ $c['val'] }}
            </p>
            <p style="font-size:11px; color:#94a3b8; margin:5px 0 0;">{{ $c['sub'] }}</p>
        </div>
        <div style="width:40px; height:40px; background:{{ $c['bg'] }}; border-radius:11px;
                    display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <i class="fa-solid {{ $c['icon'] }}" style="font-size:16px; color:{{ $c['color'] }};"></i>
        </div>
    </div>
    <div style="position:absolute; bottom:0; left:0; right:0; height:3px;
                background:{{ $c['color'] }}; opacity:.2; border-radius:0 0 14px 14px;"></div>
</div>
@endforeach
</div>

{{-- ── Bloklar ─────────────────────────────────────── --}}
<div>
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
    <h2 style="font-size:13px; font-weight:700; color:#0f172a; margin:0; text-transform:uppercase; letter-spacing:.05em;">
        Bloklar holati
    </h2>
    <a href="{{ route('blocks.index') }}" style="font-size:12px; color:#10b981; text-decoration:none; font-weight:500;">
        Barchasi →
    </a>
</div>
<div style="display:grid; grid-template-columns:repeat({{ min(count($blocks), 3) }},1fr); gap:14px;">
@foreach($blocks as $blk)
@php
    $total   = $blk->apartments_count ?: 1;
    $soldPct = round(($blk->sold_count / $total) * 100);
    $bandPct = round(($blk->reserved_count / $total) * 100);
@endphp
<a href="{{ route('apartments.block', $blk) }}" class="card"
   style="padding:18px 20px; display:block; text-decoration:none; color:inherit;
          transition:box-shadow .2s, transform .2s;"
   onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,.1)';this.style.transform='translateY(-2px)'"
   onmouseout="this.style.boxShadow='';this.style.transform=''">

    <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
        <div style="width:10px; height:10px; border-radius:50%; background:{{ $blk->color }}; flex-shrink:0;"></div>
        <span style="font-weight:700; font-size:14px; color:#0f172a; flex:1;">{{ $blk->name }}</span>
        <span style="font-size:11px; color:#94a3b8; background:#f8fafc; padding:2px 9px;
                     border-radius:99px; border:1px solid #e2e8f0;">{{ $blk->total_floors }}-qavat</span>
    </div>

    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-bottom:16px;">
        @foreach([
            [$blk->free_count,     "Bo'sh",   '#ecfdf5','#059669'],
            [$blk->reserved_count, 'Band',    '#fffbeb','#d97706'],
            [$blk->sold_count,     'Sotilgan','#fef2f2','#dc2626'],
        ] as [$cnt, $lbl, $bg, $clr])
        <div style="background:{{ $bg }}; border-radius:10px; padding:9px 6px; text-align:center;">
            <div style="font-size:20px; font-weight:700; color:{{ $clr }}; line-height:1;">{{ $cnt }}</div>
            <div style="font-size:10px; color:{{ $clr }}; margin-top:2px; opacity:.8;">{{ $lbl }}</div>
        </div>
        @endforeach
    </div>

    <div>
        <div style="display:flex; justify-content:space-between; font-size:11px; color:#94a3b8; margin-bottom:6px;">
            <span>Sotilgan <strong style="color:#374151;">{{ $soldPct }}%</strong></span>
            <span>{{ $blk->apartments_count }} xonadon</span>
        </div>
        <div style="background:#f1f5f9; border-radius:99px; height:5px; overflow:hidden;">
            <div style="height:5px; border-radius:99px; background:{{ $blk->color }};
                        width:{{ $soldPct }}%; transition:width .6s ease;"></div>
        </div>
    </div>
</a>
@endforeach
</div>
</div>

{{-- ── So'nggi to'lovlar + Muddati o'tganlar ──────── --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">

    {{-- So'nggi to'lovlar --}}
    <div class="card" style="overflow:hidden;">
        <div style="padding:14px 20px; border-bottom:1px solid #f1f5f9;
                    display:flex; align-items:center; justify-content:space-between;">
            <div style="font-weight:600; font-size:13px; display:flex; align-items:center; gap:8px; color:#0f172a;">
                <div style="width:28px; height:28px; background:#ecfdf5; border-radius:8px;
                            display:flex; align-items:center; justify-content:center;">
                    <i class="fa-solid fa-receipt" style="font-size:12px; color:#059669;"></i>
                </div>
                So'nggi to'lovlar
            </div>
            <a href="{{ route('payments.index') }}"
               style="font-size:12px; color:#10b981; text-decoration:none; font-weight:500;">Barchasi →</a>
        </div>
        @forelse($recentPayments as $pay)
        <div class="tbl-row">
            <div style="width:32px; height:32px; background:linear-gradient(135deg,#d1fae5,#a7f3d0);
                        border-radius:50%; display:flex; align-items:center; justify-content:center;
                        font-size:11px; font-weight:700; color:#065f46; flex-shrink:0;">
                {{ $pay->contract->client->initials ?? '?' }}
            </div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:13px; font-weight:500; color:#0f172a;
                            overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    {{ $pay->contract->client->full_name ?? '—' }}
                </div>
                <div style="font-size:11px; color:#94a3b8; margin-top:1px;">
                    {{ $pay->receipt_number }}
                    @if($pay->contract->apartment->block ?? null)
                    · {{ $pay->contract->apartment->block->name }}
                    @endif
                </div>
            </div>
            <div style="text-align:right; flex-shrink:0;">
                <div style="font-size:13px; font-weight:700; color:#059669;">
                    {{ number_format((float)$pay->amount,0,'.',' ') }} so'm
                </div>
                <div style="font-size:11px; color:#94a3b8;">{{ $pay->payment_date->format('d.m.Y') }}</div>
            </div>
        </div>
        @empty
        <div style="padding:40px 20px; text-align:center; color:#94a3b8; font-size:13px;">
            <i class="fa-solid fa-inbox" style="font-size:28px; display:block; margin-bottom:10px; color:#cbd5e1;"></i>
            Hali to'lov yo'q
        </div>
        @endforelse
    </div>

    {{-- Muddati o'tganlar --}}
    <div class="card" style="overflow:hidden;">
        <div style="padding:14px 20px; border-bottom:1px solid #f1f5f9;
                    display:flex; align-items:center; justify-content:space-between;">
            <div style="font-weight:600; font-size:13px; display:flex; align-items:center; gap:8px; color:#0f172a;">
                <div style="width:28px; height:28px; background:#fef2f2; border-radius:8px;
                            display:flex; align-items:center; justify-content:center;">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size:12px; color:#dc2626;"></i>
                </div>
                Muddati o'tgan
                @if($stats['overdue_schedules'] > 0)
                <span style="background:#fee2e2; color:#b91c1c; font-size:10px; font-weight:700;
                             padding:1px 7px; border-radius:99px;">{{ $stats['overdue_schedules'] }}</span>
                @endif
            </div>
            <a href="{{ route('payments.overdue') }}"
               style="font-size:12px; color:#dc2626; text-decoration:none; font-weight:500;">Barchasi →</a>
        </div>
        @forelse($overdueSchedules as $sched)
        <div class="tbl-row">
            <div style="width:32px; height:32px; background:#fef2f2; border-radius:50%;
                        display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="fa-solid fa-clock" style="color:#f87171; font-size:12px;"></i>
            </div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:13px; font-weight:500; color:#0f172a;
                            overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    {{ $sched->contract->client->full_name ?? '—' }}
                </div>
                <div style="font-size:11px; color:#94a3b8; margin-top:1px;">
                    {{ $sched->contract->contract_number }} · {{ $sched->payment_number }}-to'lov
                </div>
            </div>
            <div style="text-align:right; flex-shrink:0;">
                <div style="font-size:13px; font-weight:700; color:#dc2626;">
                    {{ number_format((float)$sched->amount,0,'.',' ') }} so'm
                </div>
                <div style="font-size:11px; color:#f87171;">
                    {{ $sched->due_date->format('d.m.Y') }}
                    <span style="background:#fee2e2; border-radius:99px; padding:1px 6px;">
                        {{ $sched->days_overdue }} kun
                    </span>
                </div>
            </div>
        </div>
        @empty
        <div style="padding:40px 20px; text-align:center; color:#94a3b8; font-size:13px;">
            <i class="fa-solid fa-circle-check" style="font-size:28px; display:block; margin-bottom:10px; color:#6ee7b7;"></i>
            Barcha to'lovlar o'z vaqtida!
        </div>
        @endforelse
    </div>

</div>
</div>
@endsection
