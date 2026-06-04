@extends('layouts.app')
@section('title', 'Bloklar boshqaruvi')
@section('heading', 'Bloklar')
@section('subheading', $blocks->count() . ' ta blok')

@section('header-actions')
@if(auth()->user()->isAdmin())
<button onclick="openCreateModal()" class="btn-primary">
    <i class="fa-solid fa-plus"></i> Yangi blok
</button>
@endif
@endsection

@section('content')
<div class="space-y-4">

{{-- Bloklar jadvali --}}
<div class="card overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase border-b border-gray-100">
            <tr>
                <th class="px-5 py-3 text-left font-semibold w-8">#</th>
                <th class="px-5 py-3 text-left font-semibold">Blok nomi</th>
                <th class="px-5 py-3 text-left font-semibold">Kod</th>
                <th class="px-5 py-3 text-left font-semibold">Manzil</th>
                <th class="px-5 py-3 text-center font-semibold">Qavatlar</th>
                <th class="px-5 py-3 text-center font-semibold">Xonadonlar</th>
                <th class="px-5 py-3 text-center font-semibold">Holat</th>
                <th class="px-5 py-3 text-right font-semibold">Amallar</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($blocks as $blk)
            <tr class="hover:bg-gray-50 transition {{ $blk->is_active ? '' : 'opacity-60' }}"
                id="blk-row-{{ $blk->id }}">
                <td class="px-5 py-3.5">
                    <span class="text-xs text-gray-400">{{ $loop->iteration }}</span>
                </td>
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3.5 h-3.5 rounded-full flex-shrink-0"
                              style="background:{{ $blk->color }}"></span>
                        <a href="{{ route('apartments.block', $blk) }}"
                           class="font-semibold hover:text-emerald-700 transition">
                            {{ $blk->name }}
                        </a>
                    </div>
                </td>
                <td class="px-5 py-3.5">
                    <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded-lg">{{ $blk->code }}</span>
                </td>
                <td class="px-5 py-3.5 text-xs text-gray-500 max-w-[180px] truncate">
                    {{ $blk->address ?? '—' }}
                </td>
                <td class="px-5 py-3.5 text-center">
                    <span class="text-sm font-semibold">{{ $blk->total_floors }}</span>
                    <span class="text-xs text-gray-400 ml-0.5">qavat</span>
                </td>
                <td class="px-5 py-3.5">
                    <div class="flex items-center justify-center gap-2 text-xs">
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                            {{ $blk->free_count }}
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 bg-amber-400 rounded-full"></span>
                            {{ $blk->reserved_count }}
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 bg-red-400 rounded-full"></span>
                            {{ $blk->sold_count }}
                        </span>
                        <span class="text-gray-400">/ {{ $blk->apartments_count }}</span>
                    </div>
                </td>
                <td class="px-5 py-3.5 text-center">
                    <button onclick="toggleActive({{ $blk->id }}, this)"
                            data-active="{{ $blk->is_active ? '1' : '0' }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold transition
                                   {{ $blk->is_active
                                      ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200'
                                      : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}"
                            {{ auth()->user()->isAdmin() ? '' : 'disabled' }}>
                        <span class="w-1.5 h-1.5 rounded-full {{ $blk->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                        {{ $blk->is_active ? 'Faol' : 'Nofaol' }}
                    </button>
                </td>
                <td class="px-5 py-3.5 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('apartments.block', $blk) }}"
                           class="text-xs text-blue-600 hover:underline">
                            Ko'rish →
                        </a>
                        @if(auth()->user()->isAdmin())
                        <button onclick="openEditModal({{ $blk->id }}, {{ json_encode($blk) }})"
                                class="text-xs text-gray-500 hover:text-gray-700 px-2 py-1 rounded-lg
                                       hover:bg-gray-100 transition">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button onclick="deleteBlock({{ $blk->id }}, '{{ $blk->name }}')"
                                class="text-xs text-red-500 hover:text-red-700 px-2 py-1 rounded-lg
                                       hover:bg-red-50 transition">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-5 py-16 text-center text-gray-400">
                    <i class="fa-solid fa-building text-4xl block mb-3 text-gray-200"></i>
                    Hali blok qo'shilmagan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Rang va raqamlar izohi --}}
<div class="flex items-center gap-4 text-xs text-gray-500 px-1">
    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-emerald-500 rounded-full"></span> Bo'sh</span>
    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-amber-400 rounded-full"></span> Band</span>
    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 bg-red-400 rounded-full"></span> Sotilgan</span>
    <span class="text-gray-400 ml-2">/ Jami xonadonlar</span>
</div>

</div>
@endsection

@push('scripts')
<script>
const COLORS = [
    '#1D9E75','#185FA5','#BA7517','#E24B4A','#7C3AED',
    '#0891B2','#BE185D','#15803D','#B45309','#374151',
];

function blockFormHtml(title, data = {}) {
    const colorOpts = COLORS.map(c => `
        <button type="button" onclick="pickColor('${c}', this)"
                class="w-7 h-7 rounded-full border-2 transition"
                style="background:${c}; border-color:${c === (data.color||'#1D9E75') ? '#111' : c}"
                data-color="${c}"></button>
    `).join('');

    return `
    <div class="p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold">${title}</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="space-y-4">
            <input type="hidden" id="bf-color" value="${data.color || '#1D9E75'}">
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Blok nomi *</label>
                    <input id="bf-name" type="text" class="form-input"
                           placeholder="1-blok, A blok..." value="${data.name || ''}" required>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">
                        Kod * <span class="text-xs font-normal text-gray-400">(shartnoma raqamida ishlatiladi)</span>
                    </label>
                    <input id="bf-code" type="text" class="form-input font-mono"
                           placeholder="B1, A, BL2..." value="${data.code || ''}" maxlength="10" required>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Qavatlar soni *</label>
                    <input id="bf-floors" type="number" min="1" max="100" class="form-input"
                           placeholder="9" value="${data.total_floors || ''}">
                </div>
                <div class="col-span-2">
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Manzil</label>
                    <input id="bf-address" type="text" class="form-input"
                           placeholder="Toshkent sh., Chilonzor tumani..." value="${data.address || ''}">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Tartib raqami</label>
                    <input id="bf-order" type="number" min="0" class="form-input"
                           placeholder="0" value="${data.sort_order ?? ''}">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Rang</label>
                    <div class="flex flex-wrap gap-2 mt-1">${colorOpts}</div>
                </div>
            </div>
            <div class="flex gap-3 justify-end pt-2">
                <button type="button" onclick="closeModal()" class="btn-secondary">Bekor</button>
                <button type="button" id="bf-submit" onclick="submitBlockForm()" class="btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Saqlash
                </button>
            </div>
        </div>
    </div>`;
}

function pickColor(color, btn) {
    document.getElementById('bf-color').value = color;
    document.querySelectorAll('[data-color]').forEach(b => {
        b.style.borderColor = b.dataset.color === color ? '#111' : b.dataset.color;
    });
}

let editingBlockId = null;

function openCreateModal() {
    editingBlockId = null;
    openModal(blockFormHtml('Yangi blok qo\'shish'));
}

function openEditModal(id, blk) {
    editingBlockId = id;
    openModal(blockFormHtml('Blokni tahrirlash', blk));
}

async function submitBlockForm() {
    const name    = document.getElementById('bf-name')?.value?.trim();
    const code    = document.getElementById('bf-code')?.value?.trim();
    const floors  = document.getElementById('bf-floors')?.value;
    const address = document.getElementById('bf-address')?.value?.trim();
    const color   = document.getElementById('bf-color')?.value;
    const order   = document.getElementById('bf-order')?.value;

    if (!name || !code || !floors) {
        showToast("Ism, kod va qavatlar soni majburiy!", 'error'); return;
    }

    const btn = document.getElementById('bf-submit');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin mr-2"></i>Saqlanmoqda...';

    const url = editingBlockId ? `/blocks/${editingBlockId}` : '/blocks';
    const method = editingBlockId ? 'PUT' : 'POST';

    const payload = { name, code, total_floors: parseInt(floors), address, color };
    if (order !== '') payload.sort_order = parseInt(order);

    const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify(payload),
    });
    const data = await res.json();

    if (data.success) {
        showToast(data.message, 'success');
        closeModal();
        setTimeout(() => location.reload(), 600);
    } else {
        const err = data.errors
            ? Object.values(data.errors).flat().join('\n')
            : (data.message ?? 'Xatolik');
        showToast(err, 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk mr-2"></i>Saqlash';
    }
}

async function toggleActive(id, btn) {
    const d = await apiPost(`/blocks/${id}/toggle-active`);
    if (d.success) {
        showToast(d.message, 'success');
        setTimeout(() => location.reload(), 500);
    } else {
        showToast(d.message ?? 'Xatolik', 'error');
    }
}

async function deleteBlock(id, name) {
    if (!confirm(`"${name}" blokini o'chirishni tasdiqlaysizmi?\n\nDiqqat: blokda xonadonlar bo'lmasligi kerak!`)) return;

    const res = await fetch(`/blocks/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
    });
    const d = await res.json();

    if (d.success) {
        showToast(d.message, 'success');
        document.getElementById(`blk-row-${id}`)?.remove();
    } else {
        showToast(d.message ?? 'Xatolik', 'error');
    }
}
</script>
@endpush
