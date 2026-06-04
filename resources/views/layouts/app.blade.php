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

:root {
    --sidebar-bg:   #0f172a;
    --sidebar-w:    240px;
    --accent:       #10b981;
    --accent-dark:  #059669;
    --bg:           #f1f5f9;
    --card-bg:      #ffffff;
    --border:       #e2e8f0;
    --text:         #0f172a;
    --text-muted:   #64748b;
    --radius:       14px;
    --radius-sm:    9px;
    --shadow:       0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
    --shadow-md:    0 4px 16px rgba(0,0,0,.08);
}

/* ── Xonadon statuslari ──────────────────────────────── */
.apt-free        { background:#ecfdf5; color:#065f46; border-color:#34d399; }
.apt-reserved    { background:#fffbeb; color:#92400e; border-color:#fbbf24; }
.apt-sold        { background:#fef2f2; color:#7f1d1d; border-color:#f87171; }
.apt-unavailable { background:#f8fafc; color:#475569; border-color:#cbd5e1; }

/* ── Apartment cell ──────────────────────────────────── */
.apt-cell {
    transition: transform .15s ease, box-shadow .15s ease;
    cursor: pointer;
    border-width: 1.5px;
    border-style: solid;
    border-radius: 10px;
    user-select: none;
}
.apt-cell:hover {
    transform: translateY(-3px) scale(1.06);
    box-shadow: 0 8px 20px rgba(0,0,0,.13);
    position: relative; z-index: 10;
}
.apt-cell.selected {
    outline: 2.5px solid #0f172a;
    outline-offset: 2px;
    box-shadow: 0 6px 20px rgba(0,0,0,.15);
    position: relative; z-index: 11;
}

/* ── Sidebar nav ─────────────────────────────────────── */
.nav-link {
    display: flex; align-items: center; gap: 9px;
    padding: 7px 10px; border-radius: 8px;
    font-size: 13px; color: #94a3b8;
    text-decoration: none;
    transition: background .15s, color .15s;
    white-space: nowrap; overflow: hidden;
}
.nav-link:hover   { background: rgba(255,255,255,.06); color: #e2e8f0; }
.nav-link.active  { background: rgba(16,185,129,.15); color: #34d399; font-weight: 600; }
.nav-link .nav-icon { width: 15px; text-align: center; font-size: 12px; flex-shrink: 0; }
.nav-section {
    font-size: 10px; font-weight: 700; color: #334155;
    text-transform: uppercase; letter-spacing: .1em;
    padding: 14px 10px 3px;
}

/* ── Buttons ─────────────────────────────────────────── */
.btn-primary {
    display: inline-flex; align-items: center; gap: 6px;
    background: var(--accent); color: #fff;
    padding: 8px 16px; border-radius: var(--radius-sm);
    font-size: 13px; font-weight: 500; border: none;
    cursor: pointer; text-decoration: none;
    transition: background .15s, transform .1s, box-shadow .15s;
    white-space: nowrap;
    box-shadow: 0 2px 8px rgba(16,185,129,.3);
}
.btn-primary:hover  { background: var(--accent-dark); color: #fff; box-shadow: 0 4px 12px rgba(16,185,129,.4); }
.btn-primary:active { transform: scale(.96); }

.btn-secondary {
    display: inline-flex; align-items: center; gap: 6px;
    background: #fff; color: #374151;
    padding: 8px 16px; border-radius: var(--radius-sm);
    font-size: 13px; font-weight: 500;
    border: 1px solid var(--border); cursor: pointer;
    text-decoration: none;
    transition: background .15s, border-color .15s, transform .1s;
    white-space: nowrap;
}
.btn-secondary:hover  { background: #f8fafc; border-color: #cbd5e1; }
.btn-secondary:active { transform: scale(.96); }

.btn-danger {
    display: inline-flex; align-items: center; gap: 6px;
    background: #fff; color: #dc2626;
    padding: 8px 16px; border-radius: var(--radius-sm);
    font-size: 13px; font-weight: 500;
    border: 1px solid #fecaca; cursor: pointer;
    text-decoration: none;
    transition: background .15s, transform .1s;
    white-space: nowrap;
}
.btn-danger:hover  { background: #fef2f2; border-color: #f87171; }
.btn-danger:active { transform: scale(.96); }

.btn-sm { padding: 5px 12px !important; font-size: 12px !important; }

/* ── Form ────────────────────────────────────────────── */
.form-input {
    width: 100%; border: 1.5px solid #e2e8f0;
    border-radius: var(--radius-sm); padding: 9px 12px;
    font-size: 13.5px; background: #fff; color: var(--text);
    outline: none; transition: border-color .15s, box-shadow .15s;
    display: block;
}
.form-input:focus {
    border-color: #34d399;
    box-shadow: 0 0 0 3px rgba(52,211,153,.12);
}
.form-input::placeholder { color: #94a3b8; }
select.form-input { appearance: auto; }

/* ── Card ────────────────────────────────────────────── */
.card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}

/* ── Badge ───────────────────────────────────────────── */
.badge {
    display: inline-flex; align-items: center;
    padding: 2px 9px; border-radius: 99px;
    font-size: 11.5px; font-weight: 600;
}

/* ── Table row ───────────────────────────────────────── */
.tbl-row {
    display: flex; align-items: center; gap: 12px;
    padding: 11px 20px; border-bottom: 1px solid #f1f5f9;
    transition: background .15s;
}
.tbl-row:last-child { border-bottom: none; }
.tbl-row:hover      { background: #f8fafc; }

/* ── Scrollbar ───────────────────────────────────────── */
::-webkit-scrollbar { width: 4px; height: 4px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

/* ── Animations ──────────────────────────────────────── */
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
@keyframes slideIn { from{transform:translateY(10px);opacity:0} to{transform:translateY(0);opacity:1} }
@keyframes fadeIn  { from{opacity:0} to{opacity:1} }
.toast-item { animation: slideIn .25s cubic-bezier(.34,1.56,.64,1); }
.page-enter { animation: fadeIn .2s ease; }
</style>
</head>

<body style="background:var(--bg); color:var(--text); margin:0;
             font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',system-ui,sans-serif;">

<div style="display:flex; height:100vh; overflow:hidden;">

{{-- ═══════════════ SIDEBAR ═════════════════════════ --}}
<aside style="width:var(--sidebar-w); min-width:var(--sidebar-w); background:var(--sidebar-bg);
              display:flex; flex-direction:column; overflow:hidden; flex-shrink:0;
              border-right:1px solid rgba(255,255,255,.04);">

    {{-- Logo --}}
    <div style="padding:18px 14px 14px; border-bottom:1px solid rgba(255,255,255,.05); flex-shrink:0;">
        <div style="display:flex; align-items:center; gap:11px;">
            <div style="width:36px; height:36px; flex-shrink:0;
                        background:linear-gradient(135deg,var(--accent),var(--accent-dark));
                        border-radius:10px; display:flex; align-items:center; justify-content:center;
                        box-shadow:0 4px 12px rgba(16,185,129,.4);">
                <i class="fa-solid fa-building" style="color:#fff; font-size:15px;"></i>
            </div>
            <div>
                <div style="font-size:14px; font-weight:700; color:#f8fafc; line-height:1.2;">UySotish Pro</div>
                <div style="font-size:10.5px; color:#475569; margin-top:1px;">Boshqaruv tizimi</div>
            </div>
        </div>
    </div>

    {{-- Nav --}}
    <nav style="flex:1; padding:8px 8px; overflow-y:auto;">

        <a href="{{ route('dashboard') }}"
           class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-chart-pie nav-icon"></i>
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
            <span style="width:8px; height:8px; border-radius:50%; background:{{ $blk->color }};
                         flex-shrink:0; display:inline-block; margin-left:3px;"></span>
            <span style="flex:1; overflow:hidden; text-overflow:ellipsis;">{{ $blk->name }}</span>
            @if($freeCount > 0)
            <span style="font-size:10px; color:#475569; background:rgba(255,255,255,.05);
                         padding:1px 6px; border-radius:99px; flex-shrink:0;">{{ $freeCount }}</span>
            @endif
        </a>
        @endforeach

        <div class="nav-section">Boshqaruv</div>

        @if(auth()->user()->isAdmin())
        <a href="{{ route('blocks.index') }}"
           class="nav-link {{ request()->routeIs('blocks.*') ? 'active' : '' }}">
            <i class="fa-solid fa-building-columns nav-icon"></i>
            <span>Bloklar</span>
        </a>
        @endif

        <a href="{{ route('contracts.index') }}"
           class="nav-link {{ request()->routeIs('contracts.*') ? 'active' : '' }}">
            <i class="fa-solid fa-file-contract nav-icon"></i>
            <span>Shartnomalar</span>
        </a>

        <a href="{{ route('payments.index') }}"
           class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
            <i class="fa-solid fa-money-bill-wave nav-icon"></i>
            <span style="flex:1;">To'lovlar</span>
            @php $overdueCount = \App\Models\PaymentSchedule::where('status','overdue')->count(); @endphp
            @if($overdueCount > 0)
            <span style="background:#dc2626; color:#fff; font-size:9.5px; font-weight:700;
                         padding:1px 6px; border-radius:99px; flex-shrink:0;">{{ $overdueCount }}</span>
            @endif
        </a>

        <a href="{{ route('clients.index') }}"
           class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
            <i class="fa-solid fa-users nav-icon"></i>
            <span>Mijozlar</span>
        </a>

        <a href="{{ route('reports.index') }}"
           class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="fa-solid fa-chart-bar nav-icon"></i>
            <span>Hisobotlar</span>
        </a>

    </nav>

    {{-- User --}}
    <div style="padding:10px 8px 12px; border-top:1px solid rgba(255,255,255,.05); flex-shrink:0;">
        <div style="display:flex; align-items:center; gap:9px; padding:8px 8px; border-radius:10px;
                    background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.05);">
            <div style="width:30px; height:30px; background:linear-gradient(135deg,#1e293b,#334155);
                        border:1.5px solid rgba(52,211,153,.4);
                        border-radius:50%; display:flex; align-items:center; justify-content:center;
                        font-size:11px; font-weight:700; color:#34d399; flex-shrink:0;">
                {{ auth()->user()->initials }}
            </div>
            <div style="flex:1; min-width:0;">
                <div style="font-size:12.5px; font-weight:600; color:#e2e8f0;
                            overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    {{ auth()->user()->name }}
                </div>
                <div style="font-size:10.5px; color:#475569;">{{ auth()->user()->role_label }}</div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" title="Chiqish"
                        style="background:none; border:none; cursor:pointer; color:#475569; padding:5px;
                               border-radius:6px; transition:color .15s, background .15s;"
                        onmouseover="this.style.color='#f87171';this.style.background='rgba(239,68,68,.1)'"
                        onmouseout="this.style.color='#475569';this.style.background='none'">
                    <i class="fa-solid fa-arrow-right-from-bracket" style="font-size:13px;"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- ═══════════════ MAIN ═══════════════════════════ --}}
<main style="flex:1; display:flex; flex-direction:column; overflow:hidden; min-width:0;">

    {{-- Topbar --}}
    <header style="background:#fff; border-bottom:1px solid var(--border);
                   padding:0 24px; height:56px; flex-shrink:0;
                   display:flex; align-items:center; justify-content:space-between;
                   box-shadow:0 1px 0 #e2e8f0;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div>
                <h1 style="font-size:15px; font-weight:700; color:var(--text); margin:0; line-height:1.3;">
                    @yield('heading', 'Dashboard')
                </h1>
                @hasSection('subheading')
                <p style="font-size:11.5px; color:var(--text-muted); margin:0; font-weight:400;">
                    @yield('subheading')
                </p>
                @endif
            </div>
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
            <div style="display:flex; align-items:center; gap:5px; font-size:11.5px; font-weight:600;
                        color:#059669; background:#ecfdf5; padding:4px 11px;
                        border-radius:99px; border:1px solid #a7f3d0;">
                <span style="width:6px; height:6px; background:#10b981; border-radius:50%;
                             animation:pulse 2s infinite; display:inline-block;"></span>
                Real-time
            </div>
            @yield('header-actions')
        </div>
    </header>

    {{-- Flash --}}
    @if(session('success') || session('error') || $errors->any())
    <div style="padding:12px 24px 0;" x-data="{show:true}" x-show="show"
         x-init="setTimeout(()=>show=false,5000)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2">
        @if(session('success'))
        <div style="background:#ecfdf5; border:1px solid #6ee7b7; color:#065f46; border-radius:var(--radius-sm);
                    padding:11px 16px; font-size:13px; display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-circle-check" style="color:#10b981; flex-shrink:0;"></i>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:var(--radius-sm);
                    padding:11px 16px; font-size:13px; display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-circle-exclamation" style="color:#ef4444; flex-shrink:0;"></i>
            {{ session('error') }}
        </div>
        @endif
        @if($errors->any())
        <div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:var(--radius-sm);
                    padding:11px 16px; font-size:13px;">
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

    {{-- Content --}}
    <div style="flex:1; overflow-y:auto; padding:22px 24px;" class="page-enter">
        @yield('content')
    </div>
</main>

</div>

{{-- MODAL --}}
<div id="modal-overlay"
     style="display:none; position:fixed; inset:0; z-index:50;
            background:rgba(15,23,42,.5); backdrop-filter:blur(4px);
            align-items:center; justify-content:center; padding:16px;">
    <div id="modal-box"
         style="background:#fff; border-radius:18px;
                box-shadow:0 25px 60px rgba(0,0,0,.2), 0 0 0 1px rgba(0,0,0,.05);
                width:100%; max-width:640px; max-height:90vh; overflow-y:auto;
                animation:slideIn .25s cubic-bezier(.34,1.2,.64,1);">
    </div>
</div>

{{-- TOAST --}}
<div id="toast-container"
     style="position:fixed; bottom:24px; right:24px; z-index:60; display:flex;
            flex-direction:column-reverse; gap:8px; pointer-events:none;"
     aria-live="polite">
</div>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

/* ── Toast ──────────────────────────────────────────── */
function showToast(message, type = 'success') {
    const cfg = {
        success: { bg:'#10b981', border:'#059669', icon:'fa-circle-check',    text:'#fff' },
        error:   { bg:'#ef4444', border:'#dc2626', icon:'fa-circle-exclamation', text:'#fff' },
        info:    { bg:'#3b82f6', border:'#2563eb', icon:'fa-circle-info',       text:'#fff' },
        warning: { bg:'#f59e0b', border:'#d97706', icon:'fa-triangle-exclamation', text:'#fff' },
    };
    const c = cfg[type] ?? cfg.success;
    const el = document.createElement('div');
    el.className = 'toast-item';
    el.style.cssText = `
        background:${c.bg}; color:${c.text}; font-size:13px; padding:11px 16px;
        border-radius:12px; border:1px solid ${c.border};
        box-shadow:0 8px 24px rgba(0,0,0,.15), 0 2px 8px rgba(0,0,0,.1);
        display:flex; align-items:flex-start; gap:10px; pointer-events:auto;
        max-width:340px; line-height:1.4; white-space:pre-line;
    `;
    el.innerHTML = `<i class="fa-solid ${c.icon}" style="flex-shrink:0; margin-top:1px;"></i><span>${message}</span>`;
    document.getElementById('toast-container').appendChild(el);
    setTimeout(() => { el.style.transition='opacity .3s, transform .3s'; el.style.opacity='0'; el.style.transform='translateX(10px)'; }, 3500);
    setTimeout(() => el.remove(), 3800);
}

/* ── Modal ──────────────────────────────────────────── */
function openModal(html) {
    const box = document.getElementById('modal-box');
    const overlay = document.getElementById('modal-overlay');
    box.innerHTML = html;
    overlay.style.display = 'flex';
    box.style.animation = 'none';
    box.offsetHeight;
    box.style.animation = 'slideIn .25s cubic-bezier(.34,1.2,.64,1)';
}
function closeModal() {
    document.getElementById('modal-overlay').style.display = 'none';
    document.getElementById('modal-box').innerHTML = '';
}
document.getElementById('modal-overlay').addEventListener('click', e => {
    if (e.target === document.getElementById('modal-overlay')) closeModal();
});

/* ── AJAX ───────────────────────────────────────────── */
async function apiPost(url, data = {}) {
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':csrf, 'Accept':'application/json' },
        body: JSON.stringify(data),
    });
    return res.json();
}

/* ── Currency ───────────────────────────────────────── */
function formatSom(amount) {
    return Number(amount).toLocaleString('uz-UZ') + " so'm";
}

/* ── Real-time ──────────────────────────────────────── */
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

/* ── Apartment detail ───────────────────────────────── */
async function loadApartmentDetail(id) {
    const res = await fetch(`/api/apartments/${id}`, { headers:{ Accept:'application/json' } });
    return res.json();
}
</script>

@stack('scripts')
</body>
</html>
