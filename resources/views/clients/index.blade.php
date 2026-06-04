@extends('layouts.app')
@section('title', 'Mijozlar')
@section('heading', 'Mijozlar')
@section('subheading', number_format($clients->total()) . ' ta mijoz')

@section('header-actions')
<button onclick="openNewClientModal()" class="btn-primary">
    <i class="fa-solid fa-user-plus"></i> Yangi mijoz
</button>
@endsection

@section('content')
<div class="space-y-4">

{{-- Qidiruv --}}
<form method="GET" class="card p-4 flex gap-3 items-end">
    <div class="flex-1">
        <label class="text-xs text-gray-500 block mb-1">Qidiruv</label>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Ism, telefon, passport seriyasi, PINFL..."
               class="form-input">
    </div>
    <button type="submit" class="btn-primary">
        <i class="fa-solid fa-magnifying-glass"></i> Qidirish
    </button>
    @if(request('search'))
    <a href="{{ route('clients.index') }}" class="btn-secondary">
        <i class="fa-solid fa-xmark"></i> Tozalash
    </a>
    @endif
</form>

{{-- Jadval --}}
<div class="card overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase border-b border-gray-100">
            <tr>
                <th class="px-4 py-3 text-left font-semibold">Mijoz</th>
                <th class="px-4 py-3 text-left font-semibold">Telefon</th>
                <th class="px-4 py-3 text-left font-semibold">Passport / PINFL</th>
                <th class="px-4 py-3 text-left font-semibold">Ish joyi</th>
                <th class="px-4 py-3 text-center font-semibold">Shartnomalar</th>
                <th class="px-4 py-3 text-center font-semibold">Qo'shilgan</th>
                <th class="px-4 py-3 w-20"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($clients as $cl)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center
                                    text-xs font-bold text-blue-700 flex-shrink-0">
                            {{ $cl->initials }}
                        </div>
                        <div class="min-w-0">
                            <a href="{{ route('clients.show', $cl) }}"
                               class="text-xs font-semibold hover:text-emerald-700 transition block truncate">
                                {{ $cl->full_name }}
                            </a>
                            @if($cl->age)
                            <p class="text-xs text-gray-400">{{ $cl->age }} yosh</p>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 text-xs">
                    {{ $cl->phone }}
                    @if($cl->phone_extra)
                    <p class="text-gray-400">{{ $cl->phone_extra }}</p>
                    @endif
                </td>
                <td class="px-4 py-3 text-xs text-gray-600">
                    {{ $cl->passport_series ?? '—' }}
                    @if($cl->pinfl)
                    <p class="text-gray-400 font-mono">{{ $cl->pinfl }}</p>
                    @endif
                </td>
                <td class="px-4 py-3 text-xs text-gray-600 max-w-[150px] truncate">
                    {{ $cl->workplace ?? '—' }}
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center justify-center w-7 h-7
                                 bg-blue-50 text-blue-700 rounded-full text-xs font-bold">
                        {{ $cl->contracts_count }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center text-xs text-gray-400">
                    {{ $cl->created_at->format('d.m.Y') }}
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('clients.show', $cl) }}"
                       class="text-xs text-blue-600 hover:underline">Ko'rish →</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-16 text-center text-gray-400">
                    <i class="fa-solid fa-users text-4xl block mb-3 text-gray-200"></i>
                    Mijoz topilmadi
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($clients->hasPages())
<div class="flex justify-between items-center text-sm text-gray-500">
    <span>Jami {{ $clients->total() }} ta</span>
    {{ $clients->links() }}
</div>
@endif

</div>
@endsection

@push('scripts')
<script>
function openNewClientModal() {
    openModal(`
    <div class="p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold">Yangi mijoz qo'shish</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form action="{{ route('clients.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">To'liq ism *</label>
                    <input name="full_name" type="text" class="form-input" required
                           placeholder="Familiya Ism Sharif">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Telefon *</label>
                    <input name="phone" type="tel" class="form-input" required
                           placeholder="+998 90 123 45 67">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Qo'shimcha tel</label>
                    <input name="phone_extra" type="tel" class="form-input" placeholder="+998 91 ...">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Passport seriyasi</label>
                    <input name="passport_series" type="text" class="form-input" placeholder="AB1234567">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">PINFL</label>
                    <input name="pinfl" type="text" class="form-input" maxlength="14"
                           placeholder="14 ta raqam">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Tug'ilgan sana</label>
                    <input name="birth_date" type="date" class="form-input">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Ish joyi</label>
                    <input name="workplace" type="text" class="form-input" placeholder="Kompaniya nomi">
                </div>
                <div class="col-span-2">
                    <label class="text-sm font-semibold text-gray-700 block mb-1.5">Manzil</label>
                    <input name="address" type="text" class="form-input"
                           placeholder="Shahar, tuman, ko'cha...">
                </div>
            </div>
            <div class="flex gap-3 justify-end pt-2">
                <button type="button" onclick="closeModal()" class="btn-secondary">Bekor</button>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-user-plus"></i> Saqlash
                </button>
            </div>
        </form>
    </div>`);
}
</script>
@endpush
