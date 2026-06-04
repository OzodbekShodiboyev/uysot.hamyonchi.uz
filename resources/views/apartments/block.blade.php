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
<div class="flex gap-5" x-data="blockPage()">

{{-- ── Chap: blok ko'rinishi ──────────────────────────────────── --}}
<div class="flex-1 min-w-0 space-y-4">

    {{-- Statistika --}}
    @php
    $statCards = [
        ['key'=>'free',    'label'=>"Bo'sh",   'text'=>'text-emerald-700','bg'=>'bg-emerald-50','border'=>'border-emerald-300'],
        ['key'=>'reserved','label'=>'Band',    'text'=>'text-amber-700',  'bg'=>'bg-amber-50',  'border'=>'border-amber-300'],
        ['key'=>'sold',    'label'=>'Sotilgan','text'=>'text-red-700',    'bg'=>'bg-red-50',    'border'=>'border-red-300'],
        ['key'=>'total',   'label'=>'Jami',    'text'=>'text-gray-700',   'bg'=>'bg-gray-100',  'border'=>'border-gray-300'],
    ];
    @endphp
    <div class="grid grid-cols-4 gap-3">
    @foreach($statCards as $s)
    @php
        $pct = $stats['total'] > 0 && $s['key'] !== 'total'
            ? round($stats[$s['key']] / $stats['total'] * 100) : null;
    @endphp
    <div class="card p-3 text-center border-t-2 {{ $s['border'] }}">
        <p class="text-xs text-gray-500 mb-1">{{ $s['label'] }}</p>
        <p class="text-xl font-bold {{ $s['text'] }}">{{ $stats[$s['key']] }}</p>
        @if($pct !== null)
        <p class="text-[10px] text-gray-400 mt-0.5">{{ $pct }}%</p>
        @else
        <p class="text-[10px] text-gray-400 mt-0.5">xonadon</p>
        @endif
    </div>
    @endforeach
    </div>

    {{-- Blok navigatsiyasi --}}
    <div class="flex gap-2 flex-wrap">
    @foreach($blocks as $blk)
    <a href="{{ route('apartments.block', $blk) }}"
       class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-sm font-medium transition
              {{ $blk->id === $block->id
                 ? 'border-2 font-semibold'
                 : 'border-gray-200 text-gray-600 hover:border-gray-300' }}"
       style="{{ $blk->id === $block->id
                 ? 'border-color:'.$blk->color.';color:'.$blk->color
                 : '' }}">
        <span class="w-2.5 h-2.5 rounded-full" style="background:{{ $blk->color }}"></span>
        {{ $blk->name }}
    </a>
    @endforeach
    </div>

    {{-- Rang legendasi --}}
    <div class="flex items-center gap-4 flex-wrap text-xs">
        <span class="text-gray-400 font-medium">Holat:</span>
        @foreach(['free'=>"Bo'sh",'reserved'=>'Band','sold'=>'Sotilgan','unavailable'=>'Mavjud emas'] as $st=>$lb)
        <div class="flex items-center gap-1.5">
            <span class="w-4 h-4 rounded apt-{{ $st }} border inline-flex items-center justify-center">
            </span>
            <span class="text-gray-600">{{ $lb }}</span>
        </div>
        @endforeach
    </div>

    {{-- Qavatlar jadvali --}}
    <div class="card overflow-hidden">
        @foreach($floors as $floor)
        <div class="flex items-start gap-3 px-4 py-3 border-b border-gray-50 last:border-b-0
                    hover:bg-gray-50/50 transition">
            <div class="w-14 pt-1.5 flex-shrink-0">
                <span class="text-xs font-semibold text-gray-400 bg-gray-100 px-2 py-1 rounded-lg">
                    {{ $floor }}-qavat
                </span>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($apartments[$floor]->sortBy('number') as $apt)
                <div class="apt-cell apt-{{ $apt->status }} border rounded-xl
                            px-3 py-2 min-w-[68px] text-center select-none"
                     data-apt-id="{{ $apt->id }}"
                     data-status="{{ $apt->status }}"
                     @click="selectApartment({{ $apt->id }})">
                    <div class="text-xs font-bold">{{ $apt->number }}</div>
                    <div class="text-[10px] opacity-70 mt-0.5">{{ $apt->rooms }}x · {{ $apt->area_total }}m²</div>
                    <div class="text-[10px] font-semibold mt-0.5" data-status-label>
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
<div class="w-80 flex-shrink-0" x-show="panelOpen" x-cloak>
    <div class="card sticky top-0 overflow-hidden" id="apt-detail-panel">
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

            const statusCls = {
                free: 'bg-emerald-100 text-emerald-800',
                reserved: 'bg-amber-100 text-amber-800',
                sold: 'bg-red-100 text-red-800',
                unavailable: 'bg-gray-100 text-gray-700',
            }[apt.status] ?? 'bg-gray-100 text-gray-700';

            const renovLabels = { none: "Ta'mirsiz", rough: "Qo'pol ta'mir", full: "Tayyor ta'mir" };

            let html = `
            <div class="px-4 py-3.5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <p class="font-bold">${apt.number}-xonadon</p>
                    <p class="text-xs text-gray-500">${block.name} · ${apt.floor}-qavat · ${apt.entrance}-pod.</p>
                </div>
                <button @click="panelOpen=false;selectedId=null"
                        class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="px-4 py-3 border-b border-gray-50">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ${statusCls}">
                    ${apt.status_label}
                </span>
            </div>

            <div class="px-4 py-3 border-b border-gray-50">
                <div class="grid grid-cols-2 gap-2">
                    ${[
                        ['Xonalar', apt.rooms + ' xona'],
                        ['Maydon', apt.area_total + ' m²'],
                        ['Karobka', Number(apt.total_price).toLocaleString('uz-UZ') + " so'm"],
                        apt.price_podklyuch
                            ? ['Podklyuch', Number(apt.price_podklyuch).toLocaleString('uz-UZ') + " so'm"]
                            : ['Ta\'mir', renovLabels[apt.renovation] ?? apt.renovation],
                    ].map(([k,v]) => `
                        <div class="bg-gray-50 rounded-lg p-2.5">
                            <p class="text-[10px] text-gray-400 mb-0.5">${k}</p>
                            <p class="text-sm font-semibold">${v}</p>
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
    <div class="p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold">Yangi xonadon qo'shish</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form action="{{ route('apartments.store') }}" method="POST">
            @csrf
            <input type="hidden" name="block_id" value="{{ $block->id }}">
            <input type="hidden" name="entrance" value="1">
            <input type="hidden" name="renovation" value="none">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">Xonadon raqami *</label>
                    <input name="number" type="text" placeholder="101" class="form-input" required>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">Qavat *</label>
                    <input name="floor" type="number" min="1" max="50" class="form-input" required>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">Xonalar soni *</label>
                    <input name="rooms" type="number" min="1" max="10" class="form-input" required>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">Maydon (m²) *</label>
                    <input name="area_total" type="number" step="0.01" min="10" class="form-input" required>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 bg-amber-400 rounded-full"></span>
                            Karobka narxi (so'm) *
                        </span>
                    </label>
                    <input name="total_price" type="number" step="1000" min="1000"
                           placeholder="Ta'mirsiz narx" class="form-input" required>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full"></span>
                            Podklyuch narxi (so'm)
                        </span>
                    </label>
                    <input name="price_podklyuch" type="number" step="1000" min="1000"
                           placeholder="Tayyor ta'mir narxi" class="form-input">
                </div>
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeModal()" class="btn-secondary">Bekor</button>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-plus"></i> Qo'shish
                </button>
            </div>
        </form>
    </div>`);
}
</script>
@endpush
