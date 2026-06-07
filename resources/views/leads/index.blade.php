@extends('layouts.app')

@section('title', 'CRM — Potensial mijozlar')
@section('heading', 'CRM')
@section('subheading', 'Potensial mijozlar va so\'rov boshqaruvi')

@php
$statuses = \App\Models\Lead::statuses();
$sources  = \App\Models\Lead::sources();
@endphp

@section('header-actions')
<a href="{{ route('leads.index', array_filter(['view' => $view === 'list' ? 'kanban' : 'list', 'q' => $search])) }}"
   class="btn-secondary btn-sm" title="{{ $view === 'list' ? 'Kanban ko\'rinish' : 'Jadval ko\'rinish' }}">
    <i class="fa-solid fa-{{ $view === 'list' ? 'table-columns' : 'list' }}"></i>
    {{ $view === 'list' ? 'Kanban' : 'Jadval' }}
</a>
<button onclick="openAddModal()" class="btn-primary btn-sm">
    <i class="fa-solid fa-plus"></i> Yangi lead
</button>
@endsection

@section('content')

{{-- ── Stats ────────────────────────────────────────── --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(110px,1fr)); gap:10px; margin-bottom:18px;">
@php
$statsBar = [
    ['key'=>'all',          'label'=>'Jami',       'icon'=>'fa-users',        'color'=>'#64748b'],
    ['key'=>'new',          'label'=>'Yangi',       'icon'=>'fa-star',         'color'=>'#3b82f6'],
    ['key'=>'hot',          'label'=>'Issiq',       'icon'=>'fa-fire',         'color'=>'#ef4444'],
    ['key'=>'consultation', 'label'=>'Maslahat',    'icon'=>'fa-comments',     'color'=>'#f59e0b'],
    ['key'=>'considering',  'label'=>"O'ylayapti",  'icon'=>'fa-clock',        'color'=>'#0ea5e9'],
    ['key'=>'converted',    'label'=>'Shartnoma',   'icon'=>'fa-check-circle', 'color'=>'#10b981'],
    ['key'=>'lost',         'label'=>'Rad etdi',    'icon'=>'fa-ban',          'color'=>'#94a3b8'],
];
@endphp
@foreach($statsBar as $s)
<div style="background:#fff; border:1.5px solid #e2e8f0; border-radius:12px; padding:10px 12px;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
        <i class="fa-solid {{ $s['icon'] }}" style="color:{{ $s['color'] }}; font-size:13px;"></i>
        @if($s['key']==='all' && $todayFollowUps > 0)
        <span style="background:#fef2f2; color:#dc2626; font-size:9px; font-weight:700;
                     padding:1px 5px; border-radius:99px;">{{ $todayFollowUps }} bugun</span>
        @endif
    </div>
    <div style="font-size:20px; font-weight:800; color:#0f172a; line-height:1;">{{ $counts[$s['key']] ?? 0 }}</div>
    <div style="font-size:10.5px; color:#64748b; margin-top:1px;">{{ $s['label'] }}</div>
</div>
@endforeach
</div>

{{-- ── Search ───────────────────────────────────────── --}}
<form method="GET" action="{{ route('leads.index') }}"
      style="display:flex; gap:8px; margin-bottom:18px;">
    <input type="hidden" name="view" value="{{ $view }}">
    <div style="position:relative; flex:1; max-width:320px;">
        <i class="fa-solid fa-search" style="position:absolute; left:10px; top:50%;
           transform:translateY(-50%); color:#94a3b8; font-size:12px; pointer-events:none;"></i>
        <input type="text" name="q" value="{{ $search }}" placeholder="Ism yoki telefon..."
               class="form-input" style="padding-left:30px;">
    </div>
    <button type="submit" class="btn-primary btn-sm">Qidirish</button>
    @if($search)
    <a href="{{ route('leads.index', ['view' => $view]) }}" class="btn-secondary btn-sm">
        <i class="fa-solid fa-xmark"></i>
    </a>
    @endif
</form>

{{-- ═══════════════ KANBAN BOARD ═══════════════════════ --}}
@if($view === 'kanban')
<div id="kanban-board"
     style="display:flex; gap:12px; overflow-x:auto; padding-bottom:16px; align-items:flex-start;
            min-height:400px;">

@foreach($kanban as $statusKey => $col)
@php $info = $col['info']; $colLeads = $col['leads']; @endphp
<div class="kanban-col"
     data-status="{{ $statusKey }}"
     ondragover="event.preventDefault(); this.classList.add('drag-over')"
     ondragleave="this.classList.remove('drag-over')"
     ondrop="handleDrop(event, '{{ $statusKey }}')"
     style="min-width:230px; max-width:260px; flex-shrink:0;
            background:#f8fafc; border-radius:14px; border:2px solid #e2e8f0;
            display:flex; flex-direction:column; transition:border-color .2s;">

    {{-- Column header --}}
    <div style="padding:10px 12px 8px; border-bottom:2px solid {{ $info['border'] }};
                display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
        <div style="display:flex; align-items:center; gap:7px;">
            <span style="width:8px; height:8px; border-radius:50%; background:{{ $info['color'] }};
                         display:inline-block; flex-shrink:0;"></span>
            <span style="font-size:12.5px; font-weight:700; color:#0f172a;">{{ $info['label'] }}</span>
        </div>
        <span style="font-size:11px; font-weight:700; background:{{ $info['bg'] }};
                     color:{{ $info['color'] }}; padding:1px 8px; border-radius:99px;
                     border:1px solid {{ $info['border'] }};">{{ count($colLeads) }}</span>
    </div>

    {{-- Cards --}}
    <div class="col-cards" style="padding:8px; display:flex; flex-direction:column; gap:7px;
                                   min-height:60px; flex:1;">
    @foreach($colLeads as $lead)
    @php
        $src     = $sources[$lead->source] ?? $sources['other'];
        $overdue = $lead->is_overdue;
    @endphp
    <div class="kanban-card"
         id="kcard-{{ $lead->id }}"
         draggable="true"
         ondragstart="handleDragStart(event, {{ $lead->id }})"
         ondragend="handleDragEnd(event)"
         data-lead="{{ htmlspecialchars(json_encode([
             'id'                  => $lead->id,
             'full_name'           => $lead->full_name,
             'phone'               => $lead->phone,
             'phone_extra'         => $lead->phone_extra ?? '',
             'source'              => $lead->source,
             'status'              => $lead->status,
             'interested_block_id' => $lead->interested_block_id,
             'rooms'               => $lead->rooms,
             'budget'              => $lead->budget ? (float)$lead->budget : null,
             'notes'               => $lead->notes ?? '',
             'next_follow_up'      => $lead->next_follow_up?->format('Y-m-d') ?? '',
         ]), ENT_QUOTES) }}"
         style="background:#fff; border:1.5px solid {{ $overdue ? '#fecaca' : '#e2e8f0' }};
                border-radius:10px; padding:10px; cursor:grab;
                box-shadow:0 1px 3px rgba(0,0,0,.05);
                transition:box-shadow .15s, transform .15s;
                {{ $overdue ? 'background:#fffbeb;' : '' }}"
         onmouseover="this.style.boxShadow='0 4px 14px rgba(0,0,0,.1)'"
         onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,.05)'">

        {{-- Name + fire --}}
        <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:6px;">
            <div style="font-size:12.5px; font-weight:700; color:#0f172a; line-height:1.3; flex:1;">
                {{ $lead->full_name }}
                @if($lead->status === 'hot')
                <i class="fa-solid fa-fire" style="color:#ef4444; font-size:10px;" title="Issiq!"></i>
                @endif
            </div>
            <div style="display:flex; gap:3px; margin-left:4px; flex-shrink:0;">
                <button onclick="openEditModalById({{ $lead->id }})"
                        style="width:22px; height:22px; border:none; background:#f1f5f9;
                               border-radius:5px; cursor:pointer; font-size:10px; color:#475569;
                               display:flex; align-items:center; justify-content:center;
                               transition:background .15s;"
                        onmouseover="this.style.background='#e2e8f0'"
                        onmouseout="this.style.background='#f1f5f9'">
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button onclick="deleteLead({{ $lead->id }}, '{{ addslashes($lead->full_name) }}')"
                        style="width:22px; height:22px; border:none; background:#fef2f2;
                               border-radius:5px; cursor:pointer; font-size:10px; color:#ef4444;
                               display:flex; align-items:center; justify-content:center;
                               transition:background .15s;"
                        onmouseover="this.style.background='#fecaca'"
                        onmouseout="this.style.background='#fef2f2'">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>

        {{-- Phone --}}
        <a href="tel:{{ $lead->phone }}"
           style="font-size:11.5px; color:#0f172a; text-decoration:none; display:block;
                  margin-bottom:4px; font-weight:500;">
            <i class="fa-solid fa-phone" style="color:#94a3b8; font-size:9px; margin-right:3px;"></i>
            {{ $lead->phone }}
        </a>

        {{-- Source --}}
        <div style="font-size:10.5px; color:#64748b; margin-bottom:4px;">
            <i class="fa-brands {{ $src['icon'] }}" style="font-size:10px; margin-right:2px;"></i>
            {{ $src['label'] }}
        </div>

        {{-- Block / Rooms / Budget --}}
        @if($lead->interestedBlock || $lead->rooms || $lead->budget)
        <div style="display:flex; flex-wrap:wrap; gap:3px; margin-bottom:4px;">
            @if($lead->interestedBlock)
            <span style="font-size:10px; background:#f0f9ff; color:#0ea5e9; padding:1px 6px;
                         border-radius:99px; border:1px solid #bae6fd; font-weight:600;">
                {{ $lead->interestedBlock->name }}
            </span>
            @endif
            @if($lead->rooms)
            <span style="font-size:10px; background:#f5f3ff; color:#7c3aed; padding:1px 6px;
                         border-radius:99px; border:1px solid #ddd6fe; font-weight:600;">
                {{ $lead->rooms }}x
            </span>
            @endif
            @if($lead->budget)
            <span style="font-size:10px; background:#ecfdf5; color:#059669; padding:1px 6px;
                         border-radius:99px; border:1px solid #a7f3d0; font-weight:600;">
                {{ number_format($lead->budget / 1000000, 0) }}mln
            </span>
            @endif
        </div>
        @endif

        {{-- Notes --}}
        @if($lead->notes)
        <div style="font-size:10.5px; color:#94a3b8; line-height:1.35;
                    overflow:hidden; display:-webkit-box; -webkit-line-clamp:2;
                    -webkit-box-orient:vertical; margin-bottom:4px;">
            {{ $lead->notes }}
        </div>
        @endif

        {{-- Follow-up date --}}
        @if($lead->next_follow_up)
        <div style="font-size:10.5px; font-weight:600; margin-top:2px;
                    color:{{ $overdue ? '#dc2626' : ($lead->next_follow_up->isToday() ? '#f59e0b' : '#64748b') }};">
            <i class="fa-solid fa-calendar{{ $overdue ? '-xmark' : '' }}" style="font-size:9px; margin-right:3px;"></i>
            {{ $lead->next_follow_up->format('d.m.Y') }}
            @if($overdue)<span style="font-size:9px;"> (kechikdi)</span>
            @elseif($lead->next_follow_up->isToday())<span style="font-size:9px;"> (bugun)</span>
            @endif
        </div>
        @endif
    </div>
    @endforeach

    {{-- Add button in column --}}
    <button onclick="openAddModalWithStatus('{{ $statusKey }}')"
            style="width:100%; padding:6px; border:1.5px dashed #cbd5e1; border-radius:8px;
                   background:transparent; color:#94a3b8; font-size:11.5px; cursor:pointer;
                   display:flex; align-items:center; justify-content:center; gap:5px;
                   transition:all .15s; margin-top:2px;"
            onmouseover="this.style.borderColor='#94a3b8'; this.style.color='#475569'; this.style.background='#fff'"
            onmouseout="this.style.borderColor='#cbd5e1'; this.style.color='#94a3b8'; this.style.background='transparent'">
        <i class="fa-solid fa-plus" style="font-size:10px;"></i> Qo'shish
    </button>
    </div>

</div>
@endforeach
</div>

{{-- ═══════════════ LIST VIEW ═══════════════════════════ --}}
@else
<div class="card" style="overflow:hidden;">
    @if($allLeads->isEmpty())
    <div style="padding:48px; text-align:center; color:#94a3b8;">
        <i class="fa-solid fa-user-slash" style="font-size:32px; margin-bottom:12px; display:block; opacity:.4;"></i>
        <div style="font-size:14px; font-weight:600;">Lead topilmadi</div>
    </div>
    @else
    <div style="display:grid; grid-template-columns:2fr 1.3fr 1fr 1fr 1.1fr 1fr 80px;
                padding:10px 16px; background:#f8fafc; border-bottom:1px solid #e2e8f0;
                font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.05em;">
        <div>Mijoz</div><div>Telefon</div><div>Manba</div>
        <div>Blok/Xona</div><div>Follow-up</div><div>Holat</div><div></div>
    </div>
    @foreach($allLeads as $lead)
    @php
        $st  = $statuses[$lead->status] ?? $statuses['new'];
        $src = $sources[$lead->source]  ?? $sources['other'];
        $overdue = $lead->is_overdue;
    @endphp
    <div id="lead-row-{{ $lead->id }}"
         data-lead="{{ htmlspecialchars(json_encode([
             'id'                  => $lead->id,
             'full_name'           => $lead->full_name,
             'phone'               => $lead->phone,
             'phone_extra'         => $lead->phone_extra ?? '',
             'source'              => $lead->source,
             'status'              => $lead->status,
             'interested_block_id' => $lead->interested_block_id,
             'rooms'               => $lead->rooms,
             'budget'              => $lead->budget ? (float)$lead->budget : null,
             'notes'               => $lead->notes ?? '',
             'next_follow_up'      => $lead->next_follow_up?->format('Y-m-d') ?? '',
         ]), ENT_QUOTES) }}"
         style="display:grid; grid-template-columns:2fr 1.3fr 1fr 1fr 1.1fr 1fr 80px;
                padding:11px 16px; border-bottom:1px solid #f1f5f9; align-items:center;
                transition:background .15s; {{ $overdue ? 'background:#fffbeb;' : '' }}"
         onmouseover="this.style.background='{{ $overdue ? '#fef3c7' : '#f8fafc' }}'"
         onmouseout="this.style.background='{{ $overdue ? '#fffbeb' : '' }}'">

        <div>
            <div style="font-size:13px; font-weight:600; color:#0f172a;">
                {{ $lead->full_name }}
                @if($lead->status==='hot')<i class="fa-solid fa-fire" style="color:#ef4444; font-size:10px;"></i>@endif
            </div>
            @if($lead->notes)<div style="font-size:11px; color:#94a3b8; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:200px;">{{ $lead->notes }}</div>@endif
        </div>
        <div>
            <a href="tel:{{ $lead->phone }}" style="font-size:12px; color:#0f172a; text-decoration:none; font-weight:500;">{{ $lead->phone }}</a>
            @if($lead->phone_extra)<div style="font-size:11px; color:#94a3b8;">{{ $lead->phone_extra }}</div>@endif
        </div>
        <div style="font-size:11.5px; color:#475569;">
            <i class="fa-brands {{ $src['icon'] }}" style="font-size:10px;"></i> {{ $src['label'] }}
        </div>
        <div style="font-size:11.5px; color:#475569;">
            @if($lead->interestedBlock)<div style="font-weight:600; color:#0f172a;">{{ $lead->interestedBlock->name }}</div>@endif
            @if($lead->rooms)<span>{{ $lead->rooms }}-xonali</span>@endif
            @if($lead->budget)<div style="color:#10b981; font-size:11px; font-weight:600;">{{ number_format($lead->budget/1000000,1) }}mln</div>@endif
        </div>
        <div>
            @if($lead->next_follow_up)
            <span style="font-size:12px; font-weight:600; color:{{ $overdue ? '#dc2626' : ($lead->next_follow_up->isToday() ? '#f59e0b' : '#475569') }};">
                {{ $lead->next_follow_up->format('d.m.Y') }}
                @if($overdue)<span style="font-size:10px;"> (kechikdi)</span>@elseif($lead->next_follow_up->isToday())<span style="font-size:10px;"> (bugun)</span>@endif
            </span>
            @else<span style="color:#cbd5e1; font-size:11px;">—</span>@endif
        </div>
        <div>
            <select onchange="quickStatus({{ $lead->id }}, this.value, true)"
                    style="font-size:11px; font-weight:600; border-radius:99px; padding:3px 8px;
                           border:1.5px solid {{ $st['border'] }};
                           background:{{ $st['bg'] }}; color:{{ $st['color'] }};
                           cursor:pointer; outline:none; appearance:none;">
                @foreach($statuses as $key => $s)
                <option value="{{ $key }}" {{ $lead->status===$key ? 'selected' : '' }} style="background:#fff; color:#0f172a;">{{ $s['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex; align-items:center; gap:4px; justify-content:flex-end;">
            <button onclick="openEditModalById({{ $lead->id }})"
                    style="width:26px; height:26px; border-radius:7px; border:1px solid #e2e8f0;
                           background:#fff; cursor:pointer; color:#475569; font-size:10px;
                           display:flex; align-items:center; justify-content:center;">
                <i class="fa-solid fa-pen"></i>
            </button>
            <button onclick="deleteLead({{ $lead->id }}, '{{ addslashes($lead->full_name) }}')"
                    style="width:26px; height:26px; border-radius:7px; border:1px solid #fecaca;
                           background:#fff; cursor:pointer; color:#ef4444; font-size:10px;
                           display:flex; align-items:center; justify-content:center;">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    </div>
    @endforeach
    @endif
</div>
@endif

@endsection

@push('scripts')
<style>
.kanban-col.drag-over {
    border-color: #10b981 !important;
    background: #f0fdf4 !important;
}
.kanban-card.dragging {
    opacity: .45;
    transform: rotate(2deg) scale(.97);
    cursor: grabbing !important;
}
</style>
<script>
const LEADS_STATUSES = @json(\App\Models\Lead::statuses());
const LEADS_SOURCES  = @json(\App\Models\Lead::sources());
const BLOCKS         = @json($blocks->map(fn($b)=>['id'=>(int)$b->id,'name'=>$b->name]));
const CURRENT_VIEW   = '{{ $view }}';

// ── All leads data embedded ──────────────────────────
const ALL_LEADS_DATA = {};
document.querySelectorAll('[data-lead]').forEach(el => {
    try {
        const d = JSON.parse(el.dataset.lead);
        ALL_LEADS_DATA[d.id] = d;
    } catch(e) {}
});

// ── Drag & Drop ──────────────────────────────────────
let draggedId = null;

function handleDragStart(e, id) {
    draggedId = id;
    e.dataTransfer.effectAllowed = 'move';
    setTimeout(() => {
        document.getElementById(`kcard-${id}`)?.classList.add('dragging');
    }, 0);
}

function handleDragEnd(e) {
    document.querySelectorAll('.kanban-card').forEach(c => c.classList.remove('dragging'));
    document.querySelectorAll('.kanban-col').forEach(c => c.classList.remove('drag-over'));
}

function handleDrop(e, newStatus) {
    e.preventDefault();
    document.querySelectorAll('.kanban-col').forEach(c => c.classList.remove('drag-over'));
    if (!draggedId) return;

    const card = document.getElementById(`kcard-${draggedId}`);
    if (!card) return;

    const currentData = ALL_LEADS_DATA[draggedId];
    if (currentData && currentData.status === newStatus) return;

    quickStatus(draggedId, newStatus, false, card, newStatus);
    draggedId = null;
}

// ── Quick status change ──────────────────────────────
async function quickStatus(id, status, reload = false, cardEl = null, newStatus = null) {
    const res  = await fetch(`/leads/${id}/status`, {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':csrf, 'Accept':'application/json' },
        body: JSON.stringify({ status }),
    });
    const data = await res.json();

    if (data.success) {
        showToast(data.message, 'success');
        if (reload) {
            setTimeout(() => location.reload(), 400);
        } else if (cardEl && CURRENT_VIEW === 'kanban') {
            // Move card to new column without reload
            const targetCol = document.querySelector(`.kanban-col[data-status="${newStatus}"] .col-cards`);
            if (targetCol) {
                // Update data
                if (ALL_LEADS_DATA[id]) ALL_LEADS_DATA[id].status = newStatus;
                // Move card DOM
                cardEl.style.transition = 'opacity .2s';
                cardEl.style.opacity = '0';
                setTimeout(() => {
                    cardEl.style.opacity = '1';
                    targetCol.insertBefore(cardEl, targetCol.querySelector('button'));
                    updateColCount(newStatus);
                    updateColCount(status);
                }, 200);
            }
        }
    } else {
        showToast(data.message ?? 'Xatolik', 'error');
        if (cardEl) location.reload();
    }
}

function updateColCount(statusKey) {
    const col   = document.querySelector(`.kanban-col[data-status="${statusKey}"]`);
    if (!col) return;
    const count = col.querySelectorAll('.kanban-card').length;
    const badge = col.querySelector('.col-cards')?.previousElementSibling?.querySelector('span:last-child');
    if (badge) badge.textContent = count;
}

// ── Modal HTML ───────────────────────────────────────
function leadFormHTML(lead, defaultStatus) {
    const isEdit = !!lead;
    const get = (f, d = '') => {
        if (!lead) return d;
        const v = lead[f];
        return (v === null || v === undefined) ? d : v;
    };

    const statusOpts = Object.entries(LEADS_STATUSES).map(([k, s]) => {
        const defStatus = lead ? get('status', 'new') : (defaultStatus || 'new');
        return `<option value="${k}" ${defStatus == k ? 'selected' : ''}>${s.label}</option>`;
    }).join('');

    const sourceOpts = Object.entries(LEADS_SOURCES).map(([k, s]) =>
        `<option value="${k}" ${get('source', 'office') == k ? 'selected' : ''}>${s.label}</option>`
    ).join('');

    const blockOpts = '<option value="">— Tanlanmagan —</option>' +
        BLOCKS.map(b =>
            `<option value="${b.id}" ${get('interested_block_id') == b.id ? 'selected' : ''}>${b.name}</option>`
        ).join('');

    const roomOpts = '<option value="">—</option>' +
        [1,2,3,4,5].map(r =>
            `<option value="${r}" ${get('rooms') == r ? 'selected' : ''}>${r}-xonali</option>`
        ).join('');

    const budgetVal = get('budget') ? Math.round(get('budget')) : '';
    const followVal = get('next_follow_up') ? String(get('next_follow_up')).split('T')[0] : '';

    return `
    <div style="padding:22px;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px;">
            <h3 style="margin:0; font-size:15px; font-weight:700; color:#0f172a;">
                ${isEdit ? 'Lead tahrirlash' : 'Yangi lead qo\'shish'}
            </h3>
            <button onclick="closeModal()" style="background:none; border:none; cursor:pointer;
                    font-size:20px; color:#94a3b8; line-height:1; padding:4px;">×</button>
        </div>

        <form id="lead-form" onsubmit="submitLead(event, ${lead ? lead.id : 'null'})">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">

                <div style="grid-column:1/-1;">
                    <label style="font-size:11.5px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">Ism Familiya *</label>
                    <input type="text" name="full_name" required value="${escHtml(get('full_name'))}"
                           placeholder="Aliyev Vohid" class="form-input">
                </div>

                <div>
                    <label style="font-size:11.5px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">Telefon *</label>
                    <input type="text" name="phone" required value="${escHtml(get('phone'))}"
                           placeholder="+998 90 123 45 67" class="form-input">
                </div>
                <div>
                    <label style="font-size:11.5px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">Qo'shimcha tel</label>
                    <input type="text" name="phone_extra" value="${escHtml(get('phone_extra'))}"
                           placeholder="+998 93 ..." class="form-input">
                </div>

                <div>
                    <label style="font-size:11.5px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">Manba *</label>
                    <select name="source" class="form-input">${sourceOpts}</select>
                </div>
                <div>
                    <label style="font-size:11.5px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">Holat *</label>
                    <select name="status" class="form-input">${statusOpts}</select>
                </div>

                <div>
                    <label style="font-size:11.5px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">Blok qiziqishi</label>
                    <select name="interested_block_id" class="form-input">${blockOpts}</select>
                </div>
                <div>
                    <label style="font-size:11.5px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">Necha xonali</label>
                    <select name="rooms" class="form-input">${roomOpts}</select>
                </div>

                <div>
                    <label style="font-size:11.5px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">Byudjet (so'm)</label>
                    <input type="number" name="budget" value="${budgetVal}"
                           placeholder="350000000" class="form-input">
                </div>
                <div>
                    <label style="font-size:11.5px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">Follow-up sanasi</label>
                    <input type="date" name="next_follow_up" value="${followVal}" class="form-input">
                </div>

                <div style="grid-column:1/-1;">
                    <label style="font-size:11.5px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">Izoh</label>
                    <textarea name="notes" rows="2" class="form-input" style="resize:vertical;"
                              placeholder="Qo'shimcha ma'lumot...">${escHtml(get('notes'))}</textarea>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:4px;">
                <button type="button" onclick="closeModal()" class="btn-secondary">Bekor</button>
                <button type="submit" class="btn-primary" id="lead-submit-btn">
                    <i class="fa-solid fa-${isEdit ? 'floppy-disk' : 'plus'}"></i>
                    ${isEdit ? 'Saqlash' : "Qo'shish"}
                </button>
            </div>
        </form>
    </div>`;
}

function escHtml(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Open modals ──────────────────────────────────────
function openAddModal()                  { openModal(leadFormHTML(null, 'new')); }
function openAddModalWithStatus(status)  { openModal(leadFormHTML(null, status)); }

function openEditModalById(id) {
    const lead = ALL_LEADS_DATA[id];
    if (!lead) { showToast('Ma\'lumot topilmadi', 'error'); return; }
    openModal(leadFormHTML(lead, null));
}

// ── Submit ───────────────────────────────────────────
async function submitLead(e, id) {
    e.preventDefault();
    const btn = document.getElementById('lead-submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saqlanmoqda...';

    const body = {};
    new FormData(e.target).forEach((v, k) => { if (v !== '') body[k] = v; });

    const url    = id ? `/leads/${id}` : '/leads';
    const method = id ? 'PUT' : 'POST';

    try {
        const res  = await fetch(url, {
            method,
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':csrf, 'Accept':'application/json' },
            body: JSON.stringify(body),
        });
        const data = await res.json();

        if (data.success) {
            showToast(data.message, 'success');
            closeModal();
            setTimeout(() => location.reload(), 500);
        } else {
            const errs = data.errors ? Object.values(data.errors).flat().join('\n') : (data.message ?? 'Xatolik');
            showToast(errs, 'error');
            btn.disabled = false;
            btn.innerHTML = id
                ? '<i class="fa-solid fa-floppy-disk"></i> Saqlash'
                : '<i class="fa-solid fa-plus"></i> Qo\'shish';
        }
    } catch(err) {
        showToast('Tarmoq xatosi: ' + err.message, 'error');
        btn.disabled = false;
    }
}

// ── Delete ───────────────────────────────────────────
async function deleteLead(id, name) {
    if (!confirm(`"${name}" — o'chirishni tasdiqlaysizmi?`)) return;
    const res  = await fetch(`/leads/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN':csrf, 'Accept':'application/json' },
    });
    const data = await res.json();
    if (data.success) {
        showToast(data.message, 'success');
        const el = document.getElementById(`kcard-${id}`) || document.getElementById(`lead-row-${id}`);
        if (el) { el.style.transition='opacity .25s'; el.style.opacity='0'; setTimeout(()=>el.remove(), 250); }
    } else {
        showToast(data.message ?? 'Xatolik', 'error');
    }
}
</script>
@endpush
