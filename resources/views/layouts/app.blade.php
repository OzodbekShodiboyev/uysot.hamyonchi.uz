<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'UySotish Pro') — UySotish Pro</title>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.3/cdn.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.3.0/pusher.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.15.3/echo.iife.min.js"></script>
<script>
Pusher.logToConsole = false;
window.Echo = new Echo({
    broadcaster: 'pusher',
    key:    '{{ config("broadcasting.connections.pusher.key") }}',
    wsHost: '{{ config("broadcasting.connections.pusher.options.host") }}',
    wsPort: {{ config("broadcasting.connections.pusher.options.port", 6001) }},
    wssPort: {{ config("broadcasting.connections.pusher.options.port", 6001) }},
    forceTLS: false, encrypted: false,
    enabledTransports: ['ws','wss'], disableStats: true, cluster: 'mt1',
});
</script>

<style>
*, *::before, *::after { box-sizing: border-box; }
[x-cloak] { display: none !important; }

/* ── Xonadon status ranglari ─────────────────────────── */
.apt-free        { background:#E1F5EE; color:#085041; border-color:#1D9E75; }
.apt-reserved    { background:#FFF3CD; color:#6B3E00; border-color:#F59E0B; }
.apt-sold        { background:#FEE2E2; color:#7f1d1d; border-color:#EF4444; }
.apt-unavailable { background:#F3F4F6; color:#374151; border-color:#9CA3AF; }

/* ── Apartment cell ──────────────────────────────────── */
.apt-cell {
    transition: transform .13s ease, box-shadow .13s ease;
    cursor: pointer;
    user-select: none;
    border-width: 1.5px;
    border-style: solid;
}
.apt-cell:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 6px 18px rgba(0,0,0,.12);
    position: relative;
    z-index: 10;
}
.apt-cell.selected {
    outline: 2.5px solid #1f2937;
    outline-offset: 2px;
    box-shadow: 0 6px 20px rgba(0,0,0,.18);
    position: relative;
    z-index: 11;
}

/* ── Nav link ────────────────────────────────────────── */
.nav-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 7px 10px;
    border-radius: 8px;
    font-size: 13.5px;
    line-height: 1.4;
    color: #4b5563;
    text-decoration: none;
    transition: background .15s, color .15s;
    width: 100%;
    white-space: nowrap;
    overflow: hidden;
}
.nav-link:hover { background: #f3f4f6; color: #111827; }
.nav-link.active {
    background: #ecfdf5;
    color: #065f46;
    font-weight: 600;
}
.nav-section {
    font-size: 10px;
    font-weight: 700;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: .08em;
    padding: 16px 10px 4px;
}

/* ── Tugmalar ────────────────────────────────────────── */
.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #059669;
    color: #fff;
    padding: 8px 16px;
    border-radius: 9px;
    font-size: 13.5px;
    font-weight: 500;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: background .15s, transform .1s;
    white-space: nowrap;
}
.btn-primary:hover { background: #047857; color: #fff; }
.btn-primary:active { transform: scale(.96); }

.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #fff;
    color: #374151;
    padding: 8px 16px;
    border-radius: 9px;
    font-size: 13.5px;
    font-weight: 500;
    border: 1px solid #d1d5db;
    cursor: pointer;
    text-decoration: none;
    transition: background .15s, transform .1s;
    white-space: nowrap;
}
.btn-secondary:hover { background: #f9fafb; }
.btn-secondary:active { transform: scale(.96); }

.btn-danger {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #fff;
    color: #dc2626;
    padding: 8px 16px;
    border-radius: 9px;
    font-size: 13.5px;
    font-weight: 500;
    border: 1px solid #fca5a5;
    cursor: pointer;
    text-decoration: none;
    transition: background .15s, transform .1s;
    white-space: nowrap;
}
.btn-danger:hover { background: #fef2f2; }
.btn-danger:active { transform: scale(.96); }

.btn-sm { padding: 5px 12px !important; font-size: 12.5px !important; }

/* ── Form input ──────────────────────────────────────── */
.form-input {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    padding: 8px 12px;
    font-size: 13.5px;
    background: #fff;
    color: #111827;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
    display: block;
}
.form-input:focus {
    border-color: #6ee7b7;
    box-shadow: 0 0 0 3px rgba(52,211,153,.18);
}
select.form-input { appearance: auto; }

/* ── Kard ────────────────────────────────────────────── */
.card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
}

/* ── Badge ───────────────────────────────────────────── */
.badge {
    display: inline-flex;
    align-items: center;
    padding: 2px 9px;
    border-radius: 9999px;
    font-size: 11.5px;
    font-weight: 600;
}

/* ── Jadval qatori ───────────────────────────────────── */
.tbl-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 20px;
    border-bottom: 1px solid #f3f4f6;
    transition: background .15s;
}
.tbl-row:last-child { border-bottom: none; }
.tbl-row:hover { background: #f9fafb; }

/* ── Scroll ──────────────────────────────────────────── */
::-webkit-scrollbar { width: 5px; height: 5px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
</style>
</head>
<body style="background:#f8fafc; color:#111827; margin:0; font-family: system-ui, -apple-system, sans-serif;">

<div style="display:flex; height:100vh; overflow:hidden;">

{{-- ═══════════════ SIDEBAR ══════════════════════ --}}
<aside style="width:232px; min-width:232px; background:#fff; border-right:1px solid #e5e7eb;
              display:flex; flex-direction:column; overflow:hidden;">

    {{-- Logo --}}
    <div style="padding:16px 18px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; gap:12px; flex-shrink:0;">
        <div style="width:38px; height:38px; background:linear-gradient(135deg,#059669,#047857);
                    border-radius:12px; display:flex; align-items:center; justify-content:center;
                    flex-shrink:0; box-shadow:0 3px 8px rgba(5,150,105,.3);">
            <i class="fa-solid fa-building" style="color:#fff; font-size:16px;"></i>
        </div>
        <div>
            <div style="font-weight:700; font-size:14px; color:#111827; line-height:1.2;">UySotish Pro</div>
            <div style="font-size:11px; color:#9ca3af; margin-top:1px;">Rivojlangan boshqaruv</div>
        </div>
    </div>

    {{-- Navigatsiya --}}
    <nav style="flex:1; padding:10px 8px; overflow-y:auto;">

        <a href="{{ route('dashboard') }}"
           class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-chart-pie" style="width:16px; text-align:center; opacity:.7; font-size:13px;"></i>
            <span>Dashboard</span>
        </a>

        <div class="nav-section">Bloklar</div>

        @foreach(\App\Models\Block::active()->get() as $blk)
        @php
            $freeCount = \App\Models\Apartment::where('block_id',$blk->id)->where('status','free')->count();
            $isActive  = request()->routeIs('apartments.block') && request()->route('block')?->id === $blk->id;
        @endphp
        <a href="{{ route('apartments.block', $blk) }}"
           class="nav-link {{ $isActive ? 'active' : '' }}">
            <span style="width:10px; height:10px; border-radius:50%; background:{{ $blk->color }}; flex-shrink:0; display:inline-block;"></span>
            <span style="flex:1; overflow:hidden; text-overflow:ellipsis;">{{ $blk->name }}</span>
            <span style="font-size:11px; color:#9ca3af; flex-shrink:0;">{{ $freeCount }} bo'sh</span>
        </a>
        @endforeach

        <div class="nav-section">Boshqaruv</div>

        @if(auth()->user()->isAdmin())
        <a href="{{ route('blocks.index') }}"
           class="nav-link {{ request()->routeIs('blocks.*') ? 'active' : '' }}">
            <i class="fa-solid fa-building-columns" style="width:16px; text-align:center; opacity:.7; font-size:13px;"></i>
            <span>Bloklar</span>
        </a>
        @endif

        <a href="{{ route('contracts.index') }}"
           class="nav-link {{ request()->routeIs('contracts.*') ? 'active' : '' }}">
            <i class="fa-solid fa-file-contract" style="width:16px; text-align:center; opacity:.7; font-size:13px;"></i>
            <span>Shartnomalar</span>
        </a>

        <a href="{{ route('payments.index') }}"
           class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
            <i class="fa-solid fa-money-bill-wave" style="width:16px; text-align:center; opacity:.7; font-size:13px;"></i>
            <span style="flex:1;">To'lovlar</span>
            @php $overdueCount = \App\Models\PaymentSchedule::where('status','overdue')->count(); @endphp
            @if($overdueCount > 0)
            <span style="background:#fee2e2; color:#b91c1c; font-size:10.5px; font-weight:700;
                         padding:1px 7px; border-radius:99px; flex-shrink:0;">{{ $overdueCount }}</span>
            @endif
        </a>

        <a href="{{ route('clients.index') }}"
           class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
            <i class="fa-solid fa-users" style="width:16px; text-align:center; opacity:.7; font-size:13px;"></i>
            <span>Mijozlar</span>
        </a>

        <a href="{{ route('reports.index') }}"
           class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="fa-solid fa-chart-bar" style="width:16px; text-align:center; opacity:.7; font-size:13px;"></i>
            <span>Hisobotlar</span>
        </a>

    </nav>

    {{-- Foydalanuvchi --}}
    <div style="padding:10px 10px 12px; border-top:1px solid #f3f4f6; flex-shrink:0;">
        <div style="display:flex; align-items:center; gap:10px; padding:6px 8px; border-radius:10px;
                    background:#f9fafb;">
            <div style="width:32px; height:32px; background:linear-gradient(135deg,#d1fae5,#6ee7b7);
                        border-radius:50%; display:flex; align-items:center; justify-content:center;
                        font-size:12px; font-weight:700; color:#065f46; flex-shrink:0;">
                {{ auth()->user()->initials }}
            </div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:13px; font-weight:600; color:#111827;
                            overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    {{ auth()->user()->name }}
                </div>
                <div style="font-size:11px; color:#9ca3af;">{{ auth()->user()->role_label }}</div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" title="Chiqish"
                        style="background:none; border:none; cursor:pointer; color:#9ca3af; padding:4px;
                               border-radius:6px; transition:color .15s, background .15s;"
                        onmouseover="this.style.color='#ef4444';this.style.background='#fee2e2'"
                        onmouseout="this.style.color='#9ca3af';this.style.background='none'">
                    <i class="fa-solid fa-arrow-right-from-bracket" style="font-size:14px;"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- ═══════════════ ASOSIY KONTENT ═══════════════ --}}
<main style="flex:1; display:flex; flex-direction:column; overflow:hidden; min-width:0;">

    {{-- Topbar --}}
    <header style="background:#fff; border-bottom:1px solid #e5e7eb; padding:12px 24px;
                   display:flex; align-items:center; justify-content:space-between;
                   flex-shrink:0; box-shadow:0 1px 3px rgba(0,0,0,.05);">
        <div>
            <h1 style="font-size:15px; font-weight:700; color:#111827; margin:0; line-height:1.3;">
                @yield('heading', 'Dashboard')
            </h1>
            @hasSection('subheading')
            <p style="font-size:12px; color:#9ca3af; margin:1px 0 0; font-weight:500;">@yield('subheading')</p>
            @endif
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
            <div style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600;
                        background:#ecfdf5; color:#065f46; padding:5px 12px; border-radius:99px;
                        border:1px solid #a7f3d0;">
                <span style="width:7px; height:7px; background:#10b981; border-radius:50%;
                             animation:pulse 2s infinite; display:inline-block;"></span>
                Real-time
            </div>
            @yield('header-actions')
        </div>
    </header>

    {{-- Flash xabarlar --}}
    @if(session('success') || session('error') || $errors->any())
    <div style="padding:12px 24px 0;" x-data="{show:true}" x-show="show"
         x-init="setTimeout(()=>show=false,4500)"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        @if(session('success'))
        <div style="background:#ecfdf5; border:1px solid #6ee7b7; color:#065f46; border-radius:12px;
                    padding:11px 16px; font-size:13.5px; display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-circle-check" style="color:#10b981;"></i>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div style="background:#fef2f2; border:1px solid #fca5a5; color:#991b1b; border-radius:12px;
                    padding:11px 16px; font-size:13.5px; display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-circle-exclamation" style="color:#ef4444;"></i>
            {{ session('error') }}
        </div>
        @endif
        @if($errors->any())
        <div style="background:#fef2f2; border:1px solid #fca5a5; color:#991b1b; border-radius:12px;
                    padding:11px 16px; font-size:13.5px;">
            <div style="font-weight:600; margin-bottom:4px; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-triangle-exclamation"></i> Xatoliklar:
            </div>
            <ul style="margin:0; padding-left:20px;">
                @foreach($errors->all() as $error)
                <li style="margin-bottom:2px;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
    @endif

    {{-- Kontent --}}
    <div style="flex:1; overflow-y:auto; padding:20px 24px;">
        @yield('content')
    </div>
</main>

</div>

{{-- GLOBAL MODAL --}}
<div id="modal-overlay"
     style="display:none; position:fixed; inset:0; z-index:50; background:rgba(0,0,0,.45);
            align-items:center; justify-content:center; padding:16px;">
    <div id="modal-box"
         style="background:#fff; border-radius:18px; box-shadow:0 20px 60px rgba(0,0,0,.2);
                width:100%; max-width:640px; max-height:90vh; overflow-y:auto;">
    </div>
</div>

{{-- TOAST --}}
<div id="toast-container"
     style="position:fixed; bottom:20px; right:20px; z-index:60; display:flex;
            flex-direction:column; gap:8px; pointer-events:none;"
     aria-live="polite">
</div>

<style>
@keyframes pulse {
    0%,100% { opacity:1; }
    50% { opacity:.4; }
}
@keyframes slideIn {
    from { transform:translateY(8px); opacity:0; }
    to   { transform:translateY(0);   opacity:1; }
}
.toast-item { animation: slideIn .25s ease; }
</style>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

/* ── Toast ─────────────────────────────────────────── */
function showToast(message, type = 'success') {
    const cfg = {
        success: { bg:'#059669', icon:'fa-circle-check' },
        error:   { bg:'#dc2626', icon:'fa-circle-exclamation' },
        info:    { bg:'#2563eb', icon:'fa-circle-info' },
        warning: { bg:'#d97706', icon:'fa-triangle-exclamation' },
    };
    const c  = cfg[type] ?? cfg.success;
    const el = document.createElement('div');
    el.className = 'toast-item';
    el.style.cssText = `background:${c.bg}; color:#fff; font-size:13.5px; padding:10px 16px;
        border-radius:12px; box-shadow:0 4px 16px rgba(0,0,0,.2);
        display:flex; align-items:center; gap:10px; pointer-events:auto; max-width:320px;`;
    el.innerHTML = `<i class="fa-solid ${c.icon}" style="flex-shrink:0;"></i><span>${message}</span>`;
    document.getElementById('toast-container').appendChild(el);
    setTimeout(() => el.style.cssText += 'opacity:0;transition:opacity .3s;', 3700);
    setTimeout(() => el.remove(), 4000);
}

/* ── Modal ─────────────────────────────────────────── */
function openModal(html) {
    document.getElementById('modal-box').innerHTML = html;
    document.getElementById('modal-overlay').style.display = 'flex';
}
function closeModal() {
    document.getElementById('modal-overlay').style.display = 'none';
    document.getElementById('modal-box').innerHTML = '';
}
document.getElementById('modal-overlay').addEventListener('click', e => {
    if (e.target === document.getElementById('modal-overlay')) closeModal();
});

/* ── AJAX ──────────────────────────────────────────── */
async function apiPost(url, data = {}) {
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':csrf, 'Accept':'application/json' },
        body: JSON.stringify(data),
    });
    return res.json();
}

/* ── Valyuta formatter (so'm) ──────────────────────── */
function formatSom(amount) {
    return Number(amount).toLocaleString('uz-UZ') + " so'm";
}

/* ── Real-time ─────────────────────────────────────── */
window.Echo.channel('apartments').listen('.status.changed', e => {
    const cell = document.querySelector(`[data-apt-id="${e.id}"]`);
    if (cell) {
        cell.className = cell.className.replace(/\bapt-\w+\b/, `apt-${e.status}`);
        cell.dataset.status = e.status;
        const label = cell.querySelector('[data-status-label]');
        if (label) label.textContent = e.statusLabel;
    }
    showToast(`#${e.number} xonadon — ${e.statusLabel}`, 'info');
});

/* ── Apartment detail ──────────────────────────────── */
async function loadApartmentDetail(id) {
    const res = await fetch(`/api/apartments/${id}`, { headers:{ Accept:'application/json' } });
    return res.json();
}
</script>

@stack('scripts')
</body>
</html>
