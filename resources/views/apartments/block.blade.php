@extends('layouts.app')
@section('title', $block->name)
@section('heading', $block->name)
@section('subheading',
    ($block->address ?? '') . ' · ' .
    $block->total_floors . '-qavat · ' .
    $stats['total'] . ' xonadon'
)

@section('header-actions')
@if(auth()->user()->isAdmin())
<button onclick="openAddApartmentModal()" class="btn-secondary">
    <i class="fa-solid fa-plus"></i> Xonadon qo'shish
</button>
@endif
<a href="{{ route('contracts.create') }}" class="btn-primary">
    <i class="fa-solid fa-file-plus"></i> Shartnoma tuzish
</a>
@endsection

@section('content')
<div style="display:flex; gap:18px;" x-data="blockPage()">

{{-- ── Chap: blok ko'rinishi ──────────────────────────────────── --}}
<div style="flex:1; min-width:0; display:flex; flex-direction:column; gap:14px;">

    {{-- Stat + Nav qatori --}}
    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">

        {{-- Stat kartochkalar --}}
        @php
        $sc = [
            ['key'=>'free',    'label'=>"Bo'sh",   'color'=>'#059669','bg'=>'#ecfdf5'],
            ['key'=>'reserved','label'=>'Band',    'color'=>'#d97706','bg'=>'#fffbeb'],
            ['key'=>'sold',    'label'=>'Sotilgan','color'=>'#dc2626','bg'=>'#fef2f2'],
            ['key'=>'total',   'label'=>'Jami',    'color'=>'#475569','bg'=>'#f8fafc'],
        ];
        @endphp
        @foreach($sc as $s)
        @php $pct = ($s['key'] !== 'total' && $stats['total'] > 0) ? round($stats[$s['key']]/$stats['total']*100) : null; @endphp
        <div style="background:{{ $s['bg'] }}; border:1px solid {{ $s['color'] }}22;
                    border-radius:12px; padding:10px 16px; text-align:center; min-width:80px;">
            <div style="font-size:20px; font-weight:700; color:{{ $s['color'] }}; line-height:1;">
                {{ $stats[$s['key']] }}
            </div>
            <div style="font-size:10.5px; color:{{ $s['color'] }}; opacity:.8; margin-top:2px; white-space:nowrap;">
                {{ $s['label'] }}{{ $pct !== null ? ' · '.$pct.'%' : '' }}
            </div>
        </div>
        @endforeach

        <div style="flex:1;"></div>

        {{-- Blok navigatsiyasi --}}
        <div style="display:flex; gap:6px; flex-wrap:wrap;">
        @foreach($blocks as $blk)
        <a href="{{ route('apartments.block', $blk) }}"
           style="display:inline-flex; align-items:center; gap:6px; padding:6px 12px;
                  border-radius:8px; font-size:12.5px; font-weight:{{ $blk->id === $block->id ? '700' : '500' }};
                  text-decoration:none; border:1.5px solid;
                  transition:box-shadow .15s;
                  border-color:{{ $blk->id === $block->id ? $blk->color : '#e2e8f0' }};
                  color:{{ $blk->id === $block->id ? $blk->color : '#64748b' }};
                  background:{{ $blk->id === $block->id ? $blk->color.'12' : '#fff' }};">
            <span style="width:8px; height:8px; border-radius:50%; background:{{ $blk->color }}; flex-shrink:0;"></span>
            {{ $blk->name }}
        </a>
        @endforeach
        </div>
    </div>

    {{-- Rang legendasi --}}
    <div style="display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
        <span style="font-size:11px; color:#94a3b8; font-weight:600; text-transform:uppercase; letter-spacing:.05em;">Holat:</span>
        @foreach(['free'=>["Bo'sh",'#059669','#ecfdf5'],'reserved'=>['Band','#d97706','#fffbeb'],'sold'=>['Sotilgan','#dc2626','#fef2f2'],'unavailable'=>['Mavjud emas','#94a3b8','#f8fafc']] as $st=>[$lb,$clr,$bg])
        <div style="display:flex; align-items:center; gap:5px; font-size:11.5px; color:#64748b;">
            <span class="apt-cell apt-{{ $st }}" style="width:28px; height:20px; border-radius:5px; display:inline-block;"></span>
            {{ $lb }}
        </div>
        @endforeach
    </div>

    {{-- Qavatlar --}}
    <div class="card" style="overflow:hidden;">
        @foreach($floors as $floor)
        <div style="display:flex; align-items:flex-start; gap:10px; padding:10px 16px;
                    border-bottom:1px solid #f8fafc; transition:background .15s;"
             onmouseover="this.style.background='#fafafa'"
             onmouseout="this.style.background=''">
            <div style="width:52px; padding-top:6px; flex-shrink:0;">
                <span style="font-size:11px; font-weight:600; color:#94a3b8; background:#f1f5f9;
                             padding:3px 8px; border-radius:7px; display:inline-block;">
                    {{ $floor }}-q
                </span>
            </div>
            <div style="display:flex; flex-wrap:wrap; gap:8px;">
                @foreach($apartments[$floor]->sortBy('number') as $apt)
                <div class="apt-cell apt-{{ $apt->status }}"
                     style="padding:10px 12px; min-width:82px; text-align:center;"
                     data-apt-id="{{ $apt->id }}"
                     data-status="{{ $apt->status }}"
                     @click="selectApartment({{ $apt->id }})">
                    <div style="font-size:15px; font-weight:800; line-height:1.2;">{{ $apt->number }}</div>
                    <div style="font-size:11px; font-weight:600; margin-top:3px;">{{ $apt->rooms }}-xonali</div>
                    <div style="font-size:10px; opacity:.7; margin-top:1px;">{{ $apt->area_total }} m²</div>
                    <div style="font-size:9.5px; font-weight:600; margin-top:2px;" data-status-label>
                        {{ $apt->status_label }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ── O'ng: tafsilot paneli ───────────────────────────────────── --}}
<div style="width:296px; flex-shrink:0;" x-show="panelOpen" x-cloak>
    <div class="card" style="position:sticky; top:0; overflow:hidden;" id="apt-detail-panel">
        {{-- Panel JS tomonidan to'ldiriladi --}}
        <div class="p-8 text-center text-gray-400">
            <i class="fa-solid fa-home text-3xl block mb-2"></i>
            Xonadon tanlang
        </div>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script>
function blockPage() {
    return {
        panelOpen: false,
        selectedId: null,

        async selectApartment(id) {
            this.panelOpen = true;
            this.selectedId = id;

            // Tanlangan katakchani belgilash
            document.querySelectorAll('[data-apt-id]').forEach(el => {
                el.classList.toggle('selected', parseInt(el.dataset.aptId) === id);
            });

            // Panel yuklanmoqda
            document.getElementById('apt-detail-panel').innerHTML = `
                <div class="p-8 text-center text-gray-400">
                    <i class="fa-solid fa-circle-notch fa-spin text-2xl block mb-2 text-emerald-500"></i>
                    Yuklanmoqda...
                </div>`;

            const apt = await loadApartmentDetail(id);
            this.renderPanel(apt);
        },

        renderPanel(apt) {
            const c     = apt.active_contract;
            const block = apt.block;

            const statusStyle = {
                free:        'background:#ecfdf5;color:#065f46;',
                reserved:    'background:#fffbeb;color:#92400e;',
                sold:        'background:#fef2f2;color:#7f1d1d;',
                unavailable: 'background:#f8fafc;color:#475569;',
            }[apt.status] ?? 'background:#f8fafc;color:#475569;';

            const fmt = n => Number(n).toLocaleString('uz-UZ');

            let html = `
            <div style="padding:14px 16px; border-bottom:1px solid #f1f5f9;
                        display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <p style="font-weight:700; font-size:15px; margin:0; color:#0f172a;">
                        #${apt.number} xonadon
                    </p>
                    <p style="font-size:11px; color:#94a3b8; margin:2px 0 0;">
                        ${block.name} · ${apt.floor}-qavat
                    </p>
                </div>
                <button @click="panelOpen=false;selectedId=null"
                        style="background:none;border:none;cursor:pointer;color:#94a3b8;
                               padding:6px;border-radius:8px;transition:color .15s,background .15s;"
                        onmouseover="this.style.background='#f1f5f9';this.style.color='#374151'"
                        onmouseout="this.style.background='none';this.style.color='#94a3b8'">
                    <i class="fa-solid fa-xmark" style="font-size:16px;"></i>
                </button>
            </div>

            <div style="padding:10px 16px; border-bottom:1px solid #f8fafc;">
                <span style="${statusStyle} padding:4px 12px; border-radius:99px;
                             font-size:11.5px; font-weight:600; display:inline-block;">
                    ${apt.status_label}
                </span>
            </div>

            <div style="padding:12px 16px; border-bottom:1px solid #f8fafc;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    ${[
                        ['Xonalar', apt.rooms + ' xona'],
                        ['Maydon', apt.area_total + ' m²'],
                        ['Karobka narx', fmt(apt.total_price) + " so'm"],
                        apt.price_podklyuch
                            ? ['Podklyuch narx', fmt(apt.price_podklyuch) + " so'm"]
                            : ['Ta\'mir', {none:"Ta'mirsiz",rough:"Qo'pol",full:"Tayyor"}[apt.renovation]||apt.renovation],
                    ].map(([k,v]) => `
                        <div style="background:#f8fafc; border-radius:9px; padding:9px 10px;
                                    border:1px solid #f1f5f9;">
                            <p style="font-size:10px; color:#94a3b8; margin:0 0 3px; font-weight:500;">${k}</p>
                            <p style="font-size:12.5px; font-weight:600; color:#0f172a; margin:0;">${v}</p>
                        </div>
                    `).join('')}
                </div>
            </div>`;

            // Egasi
            if (apt.owner) {
                html += `
            <div class="px-4 py-3 border-b border-gray-50">
                <p class="text-xs text-gray-400 font-medium mb-2">Xonadon egasi</p>
                <p class="text-sm font-semibold">${apt.owner.full_name}</p>
                <p class="text-xs text-gray-500 mt-1">${apt.owner.phone}</p>
                ${apt.owner.passport_series ? `<p class="text-xs text-gray-400">${apt.owner.passport_series}</p>` : ''}
            </div>`;
            }

            // Faol shartnoma
            if (c) {
                const prog = Math.round(parseFloat(c.paid_amount) / parseFloat(c.final_price) * 100);
                html += `
            <div class="px-4 py-3 border-b border-gray-50">
                <p class="text-xs text-gray-400 font-medium mb-2">Shartnoma</p>
                <div class="flex items-center justify-between mb-1">
                    <span class="font-mono text-xs font-bold text-blue-700">${c.contract_number}</span>
                    <a href="/contracts/${c.id}" class="text-xs text-blue-600 hover:underline">Ko'rish →</a>
                </div>
                <p class="text-sm font-medium">${c.client.full_name}</p>
                <p class="text-xs text-gray-500 mt-0.5">${c.payment_type_label}</p>
                <div class="mt-2.5">
                    <div class="flex justify-between text-xs text-gray-400 mb-1">
                        <span>To'langan: ${prog}%</span>
                        <span class="text-red-500">Qarz: ${Number(c.debt_amount).toLocaleString('uz-UZ')} so'm</span>
                    </div>
                    <div class="bg-gray-100 rounded-full h-1.5">
                        <div class="bg-emerald-500 h-1.5 rounded-full" style="width:${prog}%"></div>
                    </div>
                </div>
            </div>`;
            }

            // Tugmalar
            html += `<div class="px-4 py-3 space-y-2">`;
            if (apt.status === 'free') {
                html += `
                <button onclick="reserveApt(${apt.id})"
                        class="w-full flex items-center justify-center gap-2 border border-amber-200
                               text-amber-700 bg-amber-50 py-2 rounded-xl text-sm font-medium
                               hover:bg-amber-100 transition">
                    <i class="fa-solid fa-clock"></i> 24 soatga band qilish
                </button>
                <a href="/contracts/create?apartment_id=${apt.id}"
                   class="w-full flex items-center justify-center gap-2 bg-emerald-600 text-white
                          py-2 rounded-xl text-sm font-medium hover:bg-emerald-700 transition">
                    <i class="fa-solid fa-file-contract"></i> Shartnoma tuzish
                </a>`;
            } else if (apt.status === 'reserved') {
                html += `
                <button onclick="releaseApt(${apt.id})"
                        class="w-full flex items-center justify-center gap-2 border border-red-200
                               text-red-600 py-2 rounded-xl text-sm font-medium
                               hover:bg-red-50 transition">
                    <i class="fa-solid fa-xmark"></i> Bandlikni bekor qilish
                </button>
                <a href="/contracts/create?apartment_id=${apt.id}"
                   class="w-full flex items-center justify-center gap-2 bg-emerald-600 text-white
                          py-2 rounded-xl text-sm font-medium hover:bg-emerald-700 transition">
                    <i class="fa-solid fa-file-contract"></i> Shartnoma tuzish
                </a>`;
            } else if (c) {
                html += `
                <a href="/contracts/${c.id}"
                   class="w-full flex items-center justify-center gap-2 bg-blue-600 text-white
                          py-2 rounded-xl text-sm font-medium hover:bg-blue-700 transition">
                    <i class="fa-solid fa-file-contract"></i> Shartnomani ko'rish
                </a>`;
            }

            @if(auth()->user()->isAdmin())
            // Admin: tahrirlash va o'chirish
            html += `
            <div class="flex gap-2 pt-1 border-t border-gray-100 mt-1">
                <button onclick="openEditApartmentModal(${JSON.stringify(apt).replace(/"/g, '&quot;')})"
                        class="flex-1 flex items-center justify-center gap-1.5 border border-gray-200
                               text-gray-600 py-2 rounded-xl text-xs font-medium hover:bg-gray-50 transition">
                    <i class="fa-solid fa-pen-to-square"></i> Tahrirlash
                </button>
                <button onclick="deleteApartment(${apt.id}, '${apt.number}')"
                        class="flex-1 flex items-center justify-center gap-1.5 border border-red-200
                               text-red-600 py-2 rounded-xl text-xs font-medium hover:bg-red-50 transition">
                    <i class="fa-solid fa-trash"></i> O'chirish
                </button>
            </div>`;
            @endif

            html += `</div>`;

            document.getElementById('apt-detail-panel').innerHTML = html;
        }
    };
}

async function reserveApt(id) {
    if (!confirm("24 soatga band qilishni tasdiqlaysizmi?")) return;
    const d = await apiPost(`/apartments/${id}/reserve`, { hours: 24 });
    showToast(d.message, d.success ? 'success' : 'error');
    if (d.success) setTimeout(() => location.reload(), 600);
}

async function releaseApt(id) {
    if (!confirm("Bandlikni bekor qilishni tasdiqlaysizmi?")) return;
    const d = await apiPost(`/apartments/${id}/release`);
    showToast(d.message, d.success ? 'success' : 'error');
    if (d.success) setTimeout(() => location.reload(), 600);
}

function openAddApartmentModal() {
    openModal(`
    <div class="p-6" style="min-width:520px">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold">Xonadon qo'shish</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- Rejim tanlash --}}
        <div class="flex gap-2 mb-5 bg-gray-100 p-1 rounded-xl">
            <button onclick="switchMode('single')" id="mode-single"
                    class="flex-1 py-2 rounded-lg text-sm font-semibold transition bg-white shadow text-gray-800">
                <i class="fa-solid fa-square mr-1.5"></i> Bitta xonadon
            </button>
            <button onclick="switchMode('bulk')" id="mode-bulk"
                    class="flex-1 py-2 rounded-lg text-sm font-semibold transition text-gray-500 hover:text-gray-700">
                <i class="fa-solid fa-layer-group mr-1.5"></i> Ko'p qavatlar
            </button>
        </div>

        {{-- BITTA XONADON --}}
        <div id="form-single">
            <form action="{{ route('apartments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="block_id" value="{{ $block->id }}">
                <input type="hidden" name="entrance" value="1">
                <input type="hidden" name="renovation" value="none">
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700 block mb-1">Xonadon raqami *</label>
                        <input name="number" type="text" placeholder="101" class="form-input" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 block mb-1">Qavat *</label>
                        <input name="floor" type="number" min="1" max="100" class="form-input" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 block mb-1">Xonalar soni *</label>
                        <input name="rooms" type="number" min="1" max="10" class="form-input" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 block mb-1">Maydon (m²) *</label>
                        <input name="area_total" type="number" step="0.01" min="10" class="form-input" required>
                    </div>
                    <div class="col-span-2">
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:10px;">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                                <div style="background:#fffbeb;border-radius:8px;padding:8px;">
                                    <label style="font-size:10px;color:#92400e;font-weight:700;display:block;margin-bottom:4px;">🟡 Karobka · 0–70% avans *</label>
                                    <input name="total_price" type="number" step="1000" min="1000" placeholder="6 000 000" class="form-input" style="font-size:12px;padding:6px 10px;" required>
                                </div>
                                <div style="background:#ecfdf5;border-radius:8px;padding:8px;">
                                    <label style="font-size:10px;color:#065f46;font-weight:700;display:block;margin-bottom:4px;">🟢 Podklyuch · 0–70% avans</label>
                                    <input name="price_podklyuch" type="number" step="1000" min="1000" placeholder="6 500 000" class="form-input" style="font-size:12px;padding:6px 10px;">
                                </div>
                                <div style="background:#fffbeb;border-radius:8px;padding:8px;border:1.5px dashed #fbbf24;">
                                    <label style="font-size:10px;color:#92400e;font-weight:700;display:block;margin-bottom:4px;">🟡 Karobka · 75–100% avans</label>
                                    <input name="price_karobka_full" type="number" step="1000" min="1000" placeholder="5 000 000" class="form-input" style="font-size:12px;padding:6px 10px;">
                                </div>
                                <div style="background:#ecfdf5;border-radius:8px;padding:8px;border:1.5px dashed #34d399;">
                                    <label style="font-size:10px;color:#065f46;font-weight:700;display:block;margin-bottom:4px;">🟢 Podklyuch · 75–100% avans</label>
                                    <input name="price_podklyuch_full" type="number" step="1000" min="1000" placeholder="5 500 000" class="form-input" style="font-size:12px;padding:6px 10px;">
                                </div>
                            </div>
                            <p style="font-size:10px;color:#94a3b8;margin:6px 0 0;">75–100% avans narxlari bo'sh qolsa, 0–70% narxi ishlatiladi</p>
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="closeModal()" class="btn-secondary">Bekor</button>
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-plus"></i> Qo'shish
                    </button>
                </div>
            </form>
        </div>

        {{-- KO'P QAVATLAR --}}
        <div id="form-bulk" style="display:none">
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 mb-4 text-xs text-blue-700">
                <i class="fa-solid fa-circle-info mr-1"></i>
                Har qavatda <b>1 ta</b> shu turdagi xonadon yaratiladi. Raqam: <code>(qavat−1) × jami + pozitsiya</code>
            </div>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">Qavat: dan *</label>
                    <input id="b-floor-from" type="number" min="1" max="100" placeholder="1"
                           class="form-input" value="1" oninput="previewNumbers()">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">Qavat: gacha *</label>
                    <input id="b-floor-to" type="number" min="1" max="100" placeholder="9"
                           class="form-input" value="{{ $block->total_floors }}" oninput="previewNumbers()">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">
                        Qavatdagi jami xonadon soni *
                        <span class="text-gray-400 font-normal" style="font-size:10px;">— bino bo'yicha</span>
                    </label>
                    <input id="b-per-floor" type="number" min="1" max="20" placeholder="5"
                           class="form-input" value="5" oninput="previewNumbers()">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">
                        Qavatdagi pozitsiya *
                        <span class="text-gray-400 font-normal" style="font-size:10px;">— 1 dan jami gacha</span>
                    </label>
                    <input id="b-position" type="number" min="1" max="20" placeholder="1"
                           class="form-input" value="1" oninput="previewNumbers()">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">Xonalar soni *</label>
                    <input id="b-rooms" type="number" min="1" max="10" class="form-input">
                </div>
                <div class="col-span-2">
                    <label class="text-sm font-medium text-gray-700 block mb-1">Maydon (m²) *</label>
                    <input id="b-area" type="number" step="0.01" min="10" class="form-input">
                </div>
                <div class="col-span-2">
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:10px;">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                            <div style="background:#fffbeb;border-radius:8px;padding:8px;">
                                <label style="font-size:10px;color:#92400e;font-weight:700;display:block;margin-bottom:4px;">🟡 Karobka · 0–70% avans *</label>
                                <input id="b-price" type="number" step="1000" min="1000" placeholder="6 000 000" class="form-input" style="font-size:12px;padding:6px 10px;">
                            </div>
                            <div style="background:#ecfdf5;border-radius:8px;padding:8px;">
                                <label style="font-size:10px;color:#065f46;font-weight:700;display:block;margin-bottom:4px;">🟢 Podklyuch · 0–70% avans</label>
                                <input id="b-price-p" type="number" step="1000" min="1000" placeholder="6 500 000" class="form-input" style="font-size:12px;padding:6px 10px;">
                            </div>
                            <div style="background:#fffbeb;border-radius:8px;padding:8px;border:1.5px dashed #fbbf24;">
                                <label style="font-size:10px;color:#92400e;font-weight:700;display:block;margin-bottom:4px;">🟡 Karobka · 75–100% avans</label>
                                <input id="b-price-kf" type="number" step="1000" min="1000" placeholder="5 000 000" class="form-input" style="font-size:12px;padding:6px 10px;">
                            </div>
                            <div style="background:#ecfdf5;border-radius:8px;padding:8px;border:1.5px dashed #34d399;">
                                <label style="font-size:10px;color:#065f46;font-weight:700;display:block;margin-bottom:4px;">🟢 Podklyuch · 75–100% avans</label>
                                <input id="b-price-pf" type="number" step="1000" min="1000" placeholder="5 500 000" class="form-input" style="font-size:12px;padding:6px 10px;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-2">
                    <div id="bulk-preview" class="text-xs text-gray-400 mt-1 hidden">
                        Yaratiladi: <span id="preview-numbers" class="font-mono text-emerald-600 font-semibold"></span>
                    </div>
                </div>
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeModal()" class="btn-secondary">Bekor</button>
                <button type="button" onclick="submitBulk()" id="bulk-submit-btn" class="btn-primary">
                    <i class="fa-solid fa-layer-group"></i> Barchasini yaratish
                </button>
            </div>
        </div>
    </div>`);
}

function switchMode(mode) {
    document.getElementById('form-single').style.display = mode === 'single' ? 'block' : 'none';
    document.getElementById('form-bulk').style.display   = mode === 'bulk'   ? 'block' : 'none';
    document.getElementById('mode-single').className = mode === 'single'
        ? 'flex-1 py-2 rounded-lg text-sm font-semibold transition bg-white shadow text-gray-800'
        : 'flex-1 py-2 rounded-lg text-sm font-semibold transition text-gray-500 hover:text-gray-700';
    document.getElementById('mode-bulk').className = mode === 'bulk'
        ? 'flex-1 py-2 rounded-lg text-sm font-semibold transition bg-white shadow text-gray-800'
        : 'flex-1 py-2 rounded-lg text-sm font-semibold transition text-gray-500 hover:text-gray-700';
}

function previewNumbers() {
    const from     = parseInt(document.getElementById('b-floor-from')?.value)  || 0;
    const to       = parseInt(document.getElementById('b-floor-to')?.value)    || 0;
    const perFloor = parseInt(document.getElementById('b-per-floor')?.value)   || 0;
    const position = parseInt(document.getElementById('b-position')?.value)    || 0;
    const prev = document.getElementById('bulk-preview');
    const nums = document.getElementById('preview-numbers');
    if (!perFloor || !position || from < 1 || to < from || position > perFloor) {
        prev?.classList.add('hidden'); return;
    }

    const lines = [];
    for (let f = from; f <= to; f++) {
        const num = (f - 1) * perFloor + position;
        lines.push(`${f}-qavat: <b>${num}</b>`);
    }
    nums.innerHTML = lines.join(' · ');
    prev?.classList.remove('hidden');
}

async function submitBulk() {
    const from     = parseInt(document.getElementById('b-floor-from')?.value);
    const to       = parseInt(document.getElementById('b-floor-to')?.value);
    const perFloor = parseInt(document.getElementById('b-per-floor')?.value) || 0;
    const position = parseInt(document.getElementById('b-position')?.value)  || 0;
    const rooms  = parseInt(document.getElementById('b-rooms')?.value);
    const area   = parseFloat(document.getElementById('b-area')?.value);
    const price  = parseFloat(document.getElementById('b-price')?.value);
    const pricep  = parseFloat(document.getElementById('b-price-p')?.value)  || null;
    const pricekf = parseFloat(document.getElementById('b-price-kf')?.value) || null;
    const pricepf = parseFloat(document.getElementById('b-price-pf')?.value) || null;

    if (!from || !to || !perFloor || !position || !rooms || !area || !price) {
        showToast("Barcha majburiy maydonlarni to'ldiring!", 'error'); return;
    }
    if (position > perFloor) {
        showToast(`Pozitsiya (${position}) jami xonadon sonidan (${perFloor}) katta bo'lolmaydi!`, 'error'); return;
    }

    const btn = document.getElementById('bulk-submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin mr-2"></i>Yaratilmoqda...';

    const res = await fetch('/apartments/bulk', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({
            block_id: {{ $block->id }},
            floor_from: from, floor_to: to,
            apts_per_floor: perFloor,
            position: position,
            rooms, area_total: area,
            total_price: price,
            price_podklyuch: pricep,
            price_karobka_full: pricekf,
            price_podklyuch_full: pricepf,
        }),
    });
    const d = await res.json();

    if (d.success) {
        showToast(d.message, 'success');
        closeModal();
        setTimeout(() => location.reload(), 700);
    } else {
        const err = d.errors ? Object.values(d.errors).flat().join('\n') : (d.message ?? 'Xatolik');
        showToast(err, 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-layer-group mr-2"></i>Barchasini yaratish';
    }
}

function openEditApartmentModal(apt) {
    openModal(`
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold">#${apt.number} — Tahrirlash</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <label class="text-sm font-medium text-gray-700 block mb-1">Xonalar soni</label>
                <input id="ea-rooms" type="number" min="1" max="10" class="form-input" value="${apt.rooms}">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700 block mb-1">Maydon (m²)</label>
                <input id="ea-area" type="number" step="0.01" min="10" class="form-input" value="${apt.area_total}">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700 block mb-1">
                    <span class="w-2.5 h-2.5 bg-amber-400 rounded-full inline-block mr-1"></span>
                    Karobka narxi
                </label>
                <input id="ea-price" type="number" step="1000" min="1000" class="form-input" value="${apt.total_price}">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700 block mb-1">
                    <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full inline-block mr-1"></span>
                    Podklyuch narxi
                </label>
                <input id="ea-price-p" type="number" step="1000" min="1000" class="form-input"
                       value="${apt.price_podklyuch || ''}" placeholder="(ixtiyoriy)">
            </div>
            <div class="col-span-2">
                <label class="text-sm font-medium text-gray-700 block mb-1">Izoh</label>
                <input id="ea-notes" type="text" class="form-input" value="${apt.notes || ''}">
            </div>
        </div>
        <div class="flex gap-3 justify-end">
            <button type="button" onclick="closeModal()" class="btn-secondary">Bekor</button>
            <button type="button" onclick="saveApartment(${apt.id})" class="btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> Saqlash
            </button>
        </div>
    </div>`);
}

async function saveApartment(id) {
    const rooms  = document.getElementById('ea-rooms')?.value;
    const area   = document.getElementById('ea-area')?.value;
    const price  = document.getElementById('ea-price')?.value;
    const pricep = document.getElementById('ea-price-p')?.value || null;
    const notes  = document.getElementById('ea-notes')?.value || null;

    const res = await fetch(`/apartments/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({ rooms: parseInt(rooms), area_total: parseFloat(area),
                               total_price: parseFloat(price), price_podklyuch: pricep ? parseFloat(pricep) : null, notes }),
    });
    const d = await res.json();
    if (d.success) {
        showToast("Xonadon yangilandi", 'success');
        closeModal();
        setTimeout(() => location.reload(), 600);
    } else {
        showToast(d.message ?? 'Xatolik', 'error');
    }
}

async function deleteApartment(id, number) {
    if (!confirm(`#${number} xonadonni o'chirishni tasdiqlaysizmi?\n\nDiqqat: agar shartnoma bo'lsa o'chirilmaydi!`)) return;
    const res = await fetch(`/apartments/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
    });
    const d = await res.json();
    showToast(d.message, d.success ? 'success' : 'error');
    if (d.success) setTimeout(() => location.reload(), 600);
}
</script>
@endpush
