@extends('layouts.app')
@section('title', 'Yangi shartnoma')
@section('heading', 'Yangi shartnoma')
@section('subheading', 'Shartnoma raqami avtomatik beriladi')

@section('content')
<div class="max-w-3xl mx-auto" x-data="contractForm()">

    {{-- Tanlangan xonadon banner --}}
    @if(isset($apartment))
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 mb-5 flex items-center gap-4">
        <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-home text-emerald-600"></i>
        </div>
        <div class="flex-1">
            <p class="font-semibold text-emerald-900">{{ $apartment->full_name }}</p>
            <p class="text-sm text-emerald-700">
                {{ $apartment->rooms }} xona · {{ $apartment->area_total }} m²
                @if($apartment->price_podklyuch)
                &nbsp;·&nbsp;
                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full text-xs font-semibold">
                    Karobka: {{ number_format($apartment->total_price) }} so'm
                </span>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-semibold ml-1">
                    Podklyuch: {{ number_format($apartment->price_podklyuch) }} so'm
                </span>
                @else
                &nbsp;·&nbsp; <strong>{{ $apartment->formatted_price }}</strong>
                @endif
            </p>
        </div>
        <a href="{{ route('apartments.block', $apartment->block) }}" class="text-xs text-emerald-600 hover:underline">
            ← Orqaga
        </a>
    </div>
    @endif

    <div class="grid grid-cols-2 gap-5">

        {{-- ── CHAP USTUN ───────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- Xonadon tanlash --}}
            @unless(isset($apartment))
            <div class="card p-5">
                <label class="text-sm font-semibold text-gray-700 block mb-2">Xonadon tanlang *</label>
                <select x-model="form.apartment_id" @change="onApartmentChange($event)" class="form-input">
                    <option value="">— Blok va xonadon tanlang —</option>
                    @foreach($blocks as $blk)
                    <optgroup label="{{ $blk->name }}">
                        @foreach($blk->apartments as $apt)
                        <option value="{{ $apt->id }}"
                                data-price="{{ $apt->total_price }}"
                                data-price-podklyuch="{{ $apt->price_podklyuch ?? '' }}"
                                data-price-karobka-full="{{ $apt->price_karobka_full ?? '' }}"
                                data-price-podklyuch-full="{{ $apt->price_podklyuch_full ?? '' }}">
                            {{ $apt->number }}-xonadon · {{ $apt->floor }}-qavat · {{ $apt->rooms }}x ·
                            {{ number_format($apt->total_price) }} so'm@if($apt->price_podklyuch) / {{ number_format($apt->price_podklyuch) }} so'm@endif
                        </option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
            </div>
            @else
            <input type="hidden" x-model="form.apartment_id" value="{{ $apartment->id }}">
            @endunless

            {{-- Mijoz --}}
            <div class="card p-5">
                <h3 class="font-semibold text-sm mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-user text-gray-400"></i> Mijoz *
                </h3>
                <div class="flex gap-2 mb-2">
                    <select x-model="form.client_id" class="form-input flex-1">
                        <option value="">— Mavjud mijoz tanlang —</option>
                        @foreach($clients as $cl)
                        <option value="{{ $cl->id }}">{{ $cl->full_name }} · {{ $cl->phone }}</option>
                        @endforeach
                    </select>
                    <button @click="showNewClient = !showNewClient" type="button"
                            class="btn-secondary px-3 flex-shrink-0" title="Yangi mijoz qo'shish">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>

                {{-- Yangi mijoz --}}
                <div x-show="showNewClient" x-cloak
                     class="mt-3 border border-gray-100 rounded-xl p-4 bg-gray-50 space-y-3">
                    <p class="text-xs font-semibold text-gray-500">Yangi mijoz ma'lumotlari</p>
                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="col-span-2">
                            <input x-model="newClient.full_name" type="text"
                                   placeholder="To'liq ism (F.I.Sh.) *" class="form-input">
                        </div>
                        <input x-model="newClient.phone" type="tel"
                               placeholder="Telefon * (+998...)" class="form-input">
                        <input x-model="newClient.phone_extra" type="tel"
                               placeholder="Qo'shimcha tel (ixtiyoriy)" class="form-input">
                        <input x-model="newClient.pinfl" type="text" maxlength="14"
                               placeholder="PINFL (14 raqam)" class="form-input">
                        <input x-model="newClient.passport_series" type="text"
                               placeholder="Passport seriyasi" class="form-input">
                        <input x-model="newClient.birth_date" type="date"
                               class="form-input" title="Tug'ilgan sana">
                        <input x-model="newClient.address" type="text"
                               placeholder="Manzil" class="form-input">
                        <div class="col-span-2">
                            <input x-model="newClient.workplace" type="text"
                                   placeholder="Ish joyi" class="form-input">
                        </div>
                    </div>
                    <button @click="saveNewClient()" type="button" class="btn-primary w-full justify-center">
                        <i class="fa-solid fa-user-plus"></i> Mijozni saqlash va tanlash
                    </button>
                </div>
            </div>

            {{-- Menejer --}}
            <div class="card p-5">
                <h3 class="font-semibold text-sm mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-user-tie text-gray-400"></i> Menejer
                </h3>
                <select x-model="form.manager_id" class="form-input">
                    @foreach($managers as $m)
                    <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->role_label }})</option>
                    @endforeach
                </select>
            </div>

        </div>

        {{-- ── O'NG USTUN ──────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- Ta'mir turi (HAR DOIM KO'RINADI) --}}
            <div class="card p-5">
                <h3 class="font-semibold text-sm mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-paintbrush text-gray-400"></i> Ta'mir turi *
                </h3>
                {{-- Step 1: Ta'mir turi --}}
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex flex-col items-center gap-2 p-3 border-2 rounded-xl cursor-pointer transition"
                           :class="renovationType === 'karobka' ? 'border-amber-500 bg-amber-50' : 'border-gray-200 hover:border-amber-200'">
                        <input type="radio" x-model="renovationType" value="karobka"
                               @change="selectRenovation('karobka')" class="sr-only">
                        <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-cube text-amber-600 text-lg"></i>
                        </div>
                        <div class="text-center">
                            <p class="text-sm font-bold">Karobka</p>
                            <p class="text-[10px] text-gray-400">Ta'mirsiz</p>
                        </div>
                        <div x-show="renovationType === 'karobka'"
                             class="w-4 h-4 bg-amber-500 rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-check text-white" style="font-size:8px"></i>
                        </div>
                    </label>
                    <label class="flex flex-col items-center gap-2 p-3 border-2 rounded-xl cursor-pointer transition"
                           :class="renovationType === 'podklyuch' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200 hover:border-emerald-200'">
                        <input type="radio" x-model="renovationType" value="podklyuch"
                               @change="selectRenovation('podklyuch')" class="sr-only">
                        <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-house-chimney text-emerald-600 text-lg"></i>
                        </div>
                        <div class="text-center">
                            <p class="text-sm font-bold">Podklyuch</p>
                            <p class="text-[10px] text-gray-400">Tayyor ta'mir</p>
                        </div>
                        <div x-show="renovationType === 'podklyuch'"
                             class="w-4 h-4 bg-emerald-500 rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-check text-white" style="font-size:8px"></i>
                        </div>
                    </label>
                </div>

                {{-- Step 2: Avans foizi (faqat alohida narx bor bo'lsa) --}}
                <div x-show="renovationType && hasFullPrices()" x-cloak
                     class="mt-3 border border-gray-100 rounded-xl p-3 bg-gray-50">
                    <p class="text-xs font-semibold text-gray-500 mb-2">
                        <i class="fa-solid fa-percent mr-1"></i> Boshlang'ich to'lov ulushi
                    </p>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="selectTier('low')"
                                class="p-3 rounded-xl border-2 text-left transition"
                                :class="priceTier==='low' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                            <p class="text-xs font-bold text-gray-700">0 – 70%</p>
                            <p class="text-[10px] text-gray-400 mb-1">Bo'lib to'lash / kam avans</p>
                            <p class="text-xs font-bold text-blue-700"
                               x-text="(renovationType === 'podklyuch' ? (pricePodklyuch > 0 ? Number(pricePodklyuch).toLocaleString('uz-UZ') : '—') : Number(priceKarobka).toLocaleString('uz-UZ')) + &quot; so'm&quot;"></p>
                        </button>
                        <button type="button" @click="selectTier('high')"
                                class="p-3 rounded-xl border-2 text-left transition"
                                :class="priceTier==='high' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200 hover:border-gray-300'">
                            <p class="text-xs font-bold text-gray-700">75 – 100%</p>
                            <p class="text-[10px] text-gray-400 mb-1">Yuqori avans / to'liq</p>
                            <p class="text-xs font-bold text-emerald-700"
                               x-text="(renovationType === 'podklyuch' ? (pricePodklyuchFull > 0 ? Number(pricePodklyuchFull).toLocaleString('uz-UZ') : '—') : (priceKarobkaFull > 0 ? Number(priceKarobkaFull).toLocaleString('uz-UZ') : '—')) + &quot; so'm&quot;"></p>
                        </button>
                    </div>
                </div>
            </div>

            {{-- To'lov turi --}}
            <div class="card p-5">
                <h3 class="font-semibold text-sm mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-wallet text-gray-400"></i> To'lov turi *
                </h3>

                <div class="space-y-2 mb-4">
                    <label class="flex items-center gap-3 p-3 border-2 rounded-xl cursor-pointer transition"
                           :class="payMode==='full' ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" x-model="payMode" value="full"
                               @change="form.payment_type='full';form.initial_payment=0;form.installment_months=12;calc()"
                               class="sr-only">
                        <div class="w-9 h-9 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-circle-dollar-to-slot text-emerald-600"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold">100% to'liq to'lov</p>
                            <p class="text-xs text-gray-500">Bir yo'la to'liq to'lanadi</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 border-2 rounded-xl cursor-pointer transition"
                           :class="payMode==='with_init' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" x-model="payMode" value="with_init"
                               @change="form.payment_type='installment';calc()"
                               class="sr-only">
                        <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-percent text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold">Boshlang'ich + bo'lib to'lash</p>
                            <p class="text-xs text-gray-500">Avans to'lovi bilan</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 border-2 rounded-xl cursor-pointer transition"
                           :class="payMode==='no_init' ? 'border-amber-500 bg-amber-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" x-model="payMode" value="no_init"
                               @change="form.payment_type='installment';form.initial_payment=0;calc()"
                               class="sr-only">
                        <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-calendar-days text-amber-600"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold">Boshlang'ichsiz bo'lib to'lash</p>
                            <p class="text-xs text-gray-500">To'liq oyma-oy to'lov</p>
                        </div>
                    </label>
                </div>

                {{-- Summalar --}}
                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-gray-500 block mb-1">Umumiy narx (so'm) *</label>
                        <input x-model.number="form.total_price" type="number" min="0" step="1000"
                               @input="calc()" class="form-input">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 block mb-1">Chegirma (so'm)</label>
                        <input x-model.number="form.discount_amount" type="number" min="0" step="1000"
                               @input="calc()" class="form-input" placeholder="0">
                    </div>
                    <div x-show="payMode === 'with_init'" x-cloak>
                        <label class="text-xs text-gray-500 block mb-1">Boshlang'ich to'lov (so'm)</label>
                        <input x-model.number="form.initial_payment" type="number" min="0" step="1000"
                               @input="calc()" class="form-input" placeholder="0">
                    </div>
                    <div x-show="payMode !== 'full'" x-cloak>
                        <label class="text-xs text-gray-500 block mb-1">Muddat (oy) *</label>
                        <select x-model.number="form.installment_months" @change="calc()" class="form-input">
                            @foreach([6,12,18,24,36,48,60,72,84,96,120] as $m)
                            <option value="{{ $m }}">{{ $m }} oy ({{ round($m/12, 1) }} yil)</option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="payMode !== 'full'" x-cloak>
                        <label class="text-xs text-gray-500 block mb-1">Bo'lib to'lash boshlanishi</label>
                        <input x-model="form.installment_start_date" type="date" class="form-input">
                    </div>
                </div>

                {{-- Hisob-kitob xulosasi --}}
                <div x-show="form.total_price > 0" x-cloak
                     class="mt-4 bg-gray-50 border border-gray-100 rounded-xl p-4 space-y-2 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 flex items-center gap-1.5">
                            Narx
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold"
                                  :class="renovationType==='podklyuch'
                                          ? 'bg-emerald-100 text-emerald-700'
                                          : 'bg-amber-100 text-amber-700'"
                                  x-text="renovationType==='podklyuch' ? 'Podklyuch' : 'Karobka'">
                            </span>
                        </span>
                        <span x-text="Number(form.total_price).toLocaleString('uz-UZ') + &quot; so'm&quot;"></span>
                    </div>
                    <div x-show="form.discount_amount > 0" class="flex justify-between">
                        <span class="text-gray-500">Chegirma</span>
                        <span class="text-red-600"
                              x-text="'- ' + Number(form.discount_amount).toLocaleString('uz-UZ') + &quot; so'm&quot;"></span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-2 font-semibold">
                        <span>Yakuniy narx</span>
                        <span class="text-emerald-700"
                              x-text="Number(calc_result.finalPrice).toLocaleString('uz-UZ') + &quot; so'm&quot;"></span>
                    </div>
                    <template x-if="payMode !== 'full' && form.installment_months > 0">
                        <div class="space-y-1.5 border-t border-gray-200 pt-2">
                            <template x-if="form.initial_payment > 0">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500">Boshlang'ich</span>
                                    <span x-text="Number(form.initial_payment).toLocaleString('uz-UZ') + &quot; so'm (&quot; + calc_result.initPct + '%)'"></span>
                                </div>
                            </template>
                            <div class="flex justify-between font-semibold text-blue-700">
                                <span>Oylik to'lov</span>
                                <span x-text="Number(calc_result.monthly).toLocaleString('uz-UZ') + &quot; so'm × &quot; + form.installment_months + ' oy'"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Izoh --}}
            <div class="card p-5">
                <label class="text-sm font-semibold text-gray-700 block mb-2">Izoh</label>
                <textarea x-model="form.notes" rows="2" class="form-input resize-none"
                          placeholder="Qo'shimcha ma'lumot..."></textarea>
            </div>

            {{-- Validatsiya ogohlantirishlari --}}
            <div x-show="showHints" x-cloak
                 class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-800 space-y-1">
                <template x-if="!form.apartment_id">
                    <p><i class="fa-solid fa-circle-exclamation mr-1"></i> Xonadon tanlanmadi</p>
                </template>
                <template x-if="!form.client_id">
                    <p><i class="fa-solid fa-circle-exclamation mr-1"></i> Mijoz tanlanmadi</p>
                </template>
                <template x-if="!renovationType">
                    <p><i class="fa-solid fa-circle-exclamation mr-1"></i> Ta'mir turi tanlanmadi</p>
                </template>
                <template x-if="form.total_price <= 0">
                    <p><i class="fa-solid fa-circle-exclamation mr-1"></i> Narx kiritilmadi</p>
                </template>
                <template x-if="payMode !== 'full' && !(form.installment_months > 0)">
                    <p><i class="fa-solid fa-circle-exclamation mr-1"></i> Bo'lib to'lash muddati kiritilmadi</p>
                </template>
            </div>

            {{-- Submit --}}
            <button @click="trySubmit()"
                    class="w-full py-3.5 rounded-2xl font-semibold text-sm transition
                           flex items-center justify-center gap-2"
                    :class="isValid() && !submitting
                            ? 'bg-emerald-600 hover:bg-emerald-700 text-white'
                            : submitting
                            ? 'bg-emerald-500 text-white cursor-wait'
                            : 'bg-gray-200 text-gray-500 hover:bg-gray-300'">
                <span x-show="!submitting">
                    <i class="fa-solid fa-file-contract mr-1.5"></i> Shartnomani yaratish
                </span>
                <span x-show="submitting" x-cloak>
                    <i class="fa-solid fa-circle-notch fa-spin mr-1.5"></i> Saqlanmoqda...
                </span>
            </button>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function contractForm() {
    return {
        showNewClient:  false,
        showHints:      false,
        submitting:     false,
        payMode:        'full',
        renovationType:     '',
        priceTier:          '',   // 'low' = 0-70%, 'high' = 75-100%
        priceKarobka:       {{ isset($apartment) ? (float)$apartment->total_price : 0 }},
        pricePodklyuch:     {{ isset($apartment) ? (float)($apartment->price_podklyuch ?? 0) : 0 }},
        priceKarobkaFull:   {{ isset($apartment) ? (float)($apartment->price_karobka_full ?? 0) : 0 }},
        pricePodklyuchFull: {{ isset($apartment) ? (float)($apartment->price_podklyuch_full ?? 0) : 0 }},

        form: {
            apartment_id:           {{ isset($apartment) ? $apartment->id : 'null' }},
            client_id:              '',
            manager_id:             {{ auth()->id() }},
            payment_type:           'full',
            total_price:            0,
            discount_amount:        0,
            initial_payment:        0,
            installment_months:     12,
            installment_start_date: '',
            signed_date:            '{{ now()->toDateString() }}',
            notes:                  '',
        },

        newClient: {
            full_name: '', phone: '', phone_extra: '', pinfl: '',
            passport_series: '', birth_date: '', address: '', workplace: '',
        },

        calc_result: { finalPrice: 0, initPct: 0, remaining: 0, monthly: 0 },

        calc() {
            const fp   = Math.max(0, this.form.total_price - this.form.discount_amount);
            const init = this.payMode === 'with_init' ? this.form.initial_payment : 0;
            const rem  = Math.max(0, fp - init);
            const ms   = this.form.installment_months || 1;
            this.calc_result = {
                finalPrice: fp,
                initPct:    fp > 0 ? Math.round((init / fp) * 100 * 10) / 10 : 0,
                remaining:  rem,
                monthly:    this.payMode !== 'full' && ms > 0 ? Math.round(rem / ms) : 0,
            };
        },

        hasFullPrices() {
            return this.priceKarobkaFull > 0 || this.pricePodklyuchFull > 0;
        },

        applyPrice() {
            if (!this.renovationType) return;
            const isHigh = this.priceTier === 'high';
            if (this.renovationType === 'podklyuch') {
                const full = this.pricePodklyuchFull;
                const low  = this.pricePodklyuch;
                this.form.total_price = (isHigh && full > 0) ? full : (low > 0 ? low : this.priceKarobka);
            } else {
                const full = this.priceKarobkaFull;
                const low  = this.priceKarobka;
                this.form.total_price = (isHigh && full > 0) ? full : low;
            }
            this.form.discount_amount = 0;
            this.calc();
        },

        selectRenovation(type) {
            this.renovationType = type;
            // Agar faqat bir narx tier bo'lsa — avtomatik tanlash
            if (!this.hasFullPrices()) this.priceTier = 'low';
            this.applyPrice();
        },

        selectTier(tier) {
            this.priceTier = tier;
            this.applyPrice();
        },

        onApartmentChange(e) {
            const opt = e.target.selectedOptions[0];
            this.priceKarobka       = parseFloat(opt?.dataset.price ?? 0);
            this.pricePodklyuch     = parseFloat(opt?.dataset.pricePodklyuch ?? 0);
            this.priceKarobkaFull   = parseFloat(opt?.dataset.priceKarobkaFull ?? 0);
            this.pricePodklyuchFull = parseFloat(opt?.dataset.pricePodklyuchFull ?? 0);
            this.renovationType     = '';
            this.priceTier          = '';
            this.form.total_price   = 0;
            this.form.discount_amount = 0;
            this.calc();
        },

        isValid() {
            const needTier = this.hasFullPrices() && this.renovationType !== '';
            return this.form.apartment_id
                && this.form.client_id
                && this.renovationType !== ''
                && (!needTier || this.priceTier !== '')
                && this.form.total_price > 0
                && (this.payMode === 'full' || this.form.installment_months > 0);
        },

        trySubmit() {
            if (this.submitting) return;
            if (!this.isValid()) {
                this.showHints = true;
                if (!this.renovationType) {
                    showToast("Ta'mir turini tanlang (Karobka yoki Podklyuch)", 'warning');
                } else if (!this.form.client_id) {
                    showToast("Mijoz tanlanmadi", 'warning');
                } else if (!this.form.total_price) {
                    showToast("Narx kiritilmadi", 'warning');
                }
                return;
            }
            this.submit();
        },

        async saveNewClient() {
            if (!this.newClient.full_name || !this.newClient.phone) {
                showToast('Ism va telefon majburiy!', 'error'); return;
            }
            const r = await fetch('/clients', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body:    JSON.stringify(this.newClient),
            });
            const d = await r.json();
            if (d.success) {
                const sel = this.$el.querySelector('select[x-model="form.client_id"]');
                if (sel) {
                    const opt = new Option(`${d.client.full_name} · ${d.client.phone}`, d.client.id, true, true);
                    sel.add(opt);
                }
                this.form.client_id = String(d.client.id);
                this.showNewClient  = false;
                showToast(`Mijoz ${d.client.full_name} qo'shildi`, 'success');
            } else {
                const errMsg = d.errors
                    ? Object.values(d.errors).flat().join('\n')
                    : (d.message ?? 'Xatolik yuz berdi');
                showToast(errMsg, 'error');
            }
        },

        async submit() {
            this.submitting = true;
            const payload = { ...this.form };
            if (this.payMode === 'no_init') payload.initial_payment = 0;
            if (this.payMode === 'full')    { payload.installment_months = null; payload.initial_payment = 0; }

            try {
                const r = await fetch('/contracts', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body:    JSON.stringify(payload),
                });
                const d = await r.json();
                if (d.success) {
                    showToast(`Shartnoma №${d.contract_number} yaratildi!`, 'success');
                    setTimeout(() => window.location.href = d.redirect, 800);
                } else {
                    const errMsg = d.errors
                        ? Object.values(d.errors).flat().join('\n')
                        : (d.message ?? 'Xatolik yuz berdi');
                    showToast(errMsg, 'error');
                    this.submitting = false;
                }
            } catch {
                showToast('Serverga ulanishda xatolik', 'error');
                this.submitting = false;
            }
        },

        init() { this.calc(); },
    };
}
</script>
@endpush
