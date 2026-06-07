<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="apple-touch-icon" sizes="512x512" href="/favicon-512.png">
    <link rel="preload" as="image" href="{{ route('site.logo') }}">
    <title>@yield('title') – DataTel Admin</title>
    @include('layouts.portal-styles')
    {{-- Apply sidebar collapsed state before first paint to prevent transition flicker --}}
    <script>if(localStorage.getItem('adminSidebarCollapsed')==='1')document.documentElement.classList.add('_sb-pre')</script>
    {{-- Apply dark mode before first paint to prevent flash of light theme --}}
    <script>if(localStorage.getItem('adminDarkMode')==='1')document.documentElement.classList.add('dark')</script>
    <style>
    html._sb-pre .sidebar{width:64px;transition:none!important;}
    html._sb-pre .sidebar-logo{top:-6px;margin-bottom:-6px;border-radius:0 0 10px 10px;box-shadow:0 8px 22px rgba(0,0,0,.18);border-bottom-color:transparent;z-index:3;transition:none!important;}
    html._sb-pre .sidebar-toggle svg{transform:rotate(180deg);}
    html._sb-pre .nav-label{display:none!important;}
    html._sb-pre .nav-divider{opacity:0;margin:.15rem 0!important;}
    html._sb-pre .nav-badge{position:absolute!important;top:5px;right:5px;min-width:15px;height:15px;font-size:.55rem;padding:0 3px;}
    html._sb-pre .sidebar-nav a{padding:.65rem 0;justify-content:center;}
    html._sb-pre .sidebar-nav a.active{border-left-color:transparent!important;background:rgba(46,134,193,.12);border-radius:8px;margin:0 6px;width:calc(100% - 12px);}
    html._sb-pre .sidebar-nav a svg{width:27px!important;height:27px!important;}
    html._sb-pre .sidebar-user-info{display:none!important;}
    html._sb-pre .sidebar-user{justify-content:center;padding:.75rem 0;}
    </style>
</head>
<body class="admin-layout">

@php
    $__u          = auth()->user();
    $__initials   = collect(explode(' ', $__u->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
    $__photoPath  = $__u->profile_photo ? storage_path('app/profile-photos/'.$__u->profile_photo) : null;
    $pendingCount     = \App\Models\User::where('role','customer')->where('status','pending')->count();
    $newInquiryCount  = \App\Models\Inquiry::where('status','new')->count();
    $pendingCompanyCount = \App\Models\Company::where('status','pending')->count()
        + \App\Models\CompanyMember::where('status','pending')
              ->whereHas('company', fn($q) => $q->where('status','active'))
              ->count();
    $activeInvoiceCount = \App\Models\Invoice::whereIn('status', [
        \App\Models\Invoice::STATUS_DRAFT,
        \App\Models\Invoice::STATUS_ISSUED,
        \App\Models\Invoice::STATUS_PAYMENT_RECEIVED,
    ])->count();
    $activeWoCount = \App\Models\WorkOrder::whereIn('status', [
        \App\Models\WorkOrder::STATUS_NEW,
        \App\Models\WorkOrder::STATUS_TRIAGED,
        \App\Models\WorkOrder::STATUS_SCHEDULED,
        \App\Models\WorkOrder::STATUS_AWAITING_FEEDBACK,
        \App\Models\WorkOrder::STATUS_SERVICES_PERFORMED,
        \App\Models\WorkOrder::STATUS_INVOICE_PREPARED,
        \App\Models\WorkOrder::STATUS_BILLED,
    ])->count();
@endphp

<aside class="sidebar">
    <button class="sidebar-toggle" onclick="toggleSidebar()" title="Collapse sidebar"
            style="position:absolute;top:8px;right:8px;z-index:10;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
    <div class="sidebar-logo">
        <a href="{{ route('home') }}" style="display:block;line-height:0;">
            <img src="{{ route('site.logo') }}" alt="DataTel">
        </a>
    </div>

    <nav class="sidebar-nav">

        {{-- Dashboard --}}
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" style="display:flex;align-items:center;gap:.65rem;" data-tooltip="Dashboard">
            <svg style="flex-shrink:0;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            <span class="nav-label" style="flex:1;">Dashboard</span>
        </a>

        {{-- Work Orders --}}
        <a href="{{ route('admin.work-orders.index') }}" class="{{ request()->routeIs('admin.work-orders*') ? 'active' : '' }}" style="display:flex;align-items:center;gap:.65rem;" data-tooltip="Work Orders">
            <svg style="flex-shrink:0;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/></svg>
            <span class="nav-label" style="flex:1;">Work Orders</span>
            <span id="nav-badge-wo" class="nav-badge" style="background:var(--accent);color:#fff;border-radius:999px;font-size:.63rem;font-weight:700;min-width:18px;height:18px;display:{{ $activeWoCount > 0 ? 'inline-flex' : 'none' }};align-items:center;justify-content:center;padding:0 4px;flex-shrink:0;">{{ $activeWoCount ?: '' }}</span>
        </a>

        {{-- Invoicing --}}
        <a href="{{ route('admin.invoices.index') }}" class="{{ request()->routeIs('admin.invoices*') ? 'active' : '' }}" style="display:flex;align-items:center;gap:.65rem;" data-tooltip="Invoicing">
            <svg style="flex-shrink:0;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="13" x2="12" y2="17"/><line x1="10" y1="15" x2="14" y2="15"/></svg>
            <span class="nav-label" style="flex:1;">Invoicing</span>
            <span id="nav-badge-invoice" class="nav-badge" style="background:var(--accent);color:#fff;border-radius:999px;font-size:.63rem;font-weight:700;min-width:18px;height:18px;display:{{ $activeInvoiceCount > 0 ? 'inline-flex' : 'none' }};align-items:center;justify-content:center;padding:0 4px;flex-shrink:0;">{{ $activeInvoiceCount ?: '' }}</span>
        </a>

        <div class="nav-divider" style="height:1px;background:#e9ecef;margin:.35rem 1.25rem;"></div>

        {{-- Company Analytics --}}
        <a href="{{ route('admin.analytics.companies') }}" class="{{ request()->routeIs('admin.analytics.companies*', 'admin.companies*') ? 'active' : '' }}" style="display:flex;align-items:center;gap:.65rem;" data-tooltip="Company Analytics">
            <svg style="flex-shrink:0;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><line x1="9" y1="9" x2="9" y2="9.01"/><line x1="9" y1="12" x2="9" y2="12.01"/><line x1="9" y1="15" x2="9" y2="15.01"/></svg>
            <span class="nav-label" style="flex:1;">Company Analytics</span>
            <span id="nav-badge-company" class="nav-badge" style="background:#dc2626;color:#fff;border-radius:999px;font-size:.63rem;font-weight:700;min-width:18px;height:18px;display:{{ $pendingCompanyCount > 0 ? 'inline-flex' : 'none' }};align-items:center;justify-content:center;padding:0 4px;flex-shrink:0;">{{ $pendingCompanyCount ?: '' }}</span>
        </a>

        {{-- Customer Analytics --}}
        <a href="{{ route('admin.analytics.customers') }}" class="{{ request()->routeIs('admin.analytics.customers*') ? 'active' : '' }}" style="display:flex;align-items:center;gap:.65rem;" data-tooltip="Customer Analytics">
            <svg style="flex-shrink:0;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span class="nav-label" style="flex:1;">Customer Analytics</span>
        </a>

        {{-- Pending Accounts --}}
        <a href="{{ route('admin.pending-customers.index') }}" class="{{ request()->routeIs('admin.pending-customers*') ? 'active' : '' }}" style="display:flex;align-items:center;gap:.65rem;" data-tooltip="Pending Accounts">
            <svg style="flex-shrink:0;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><circle cx="18" cy="8" r="3"/><line x1="18" y1="6" x2="18" y2="8.5"/><line x1="18" y1="8.5" x2="19.5" y2="8.5"/></svg>
            <span class="nav-label" style="flex:1;">Pending Accounts</span>
            <span id="nav-badge-pending" class="nav-badge" style="background:#dc2626;color:#fff;border-radius:999px;font-size:.63rem;font-weight:700;min-width:18px;height:18px;display:{{ $pendingCount > 0 ? 'inline-flex' : 'none' }};align-items:center;justify-content:center;padding:0 4px;flex-shrink:0;">{{ $pendingCount ?: '' }}</span>
        </a>

        <div class="nav-divider" style="height:1px;background:#e9ecef;margin:.35rem 1.25rem;"></div>

        {{-- Reports --}}
        <a href="{{ route('admin.reports') }}" class="{{ request()->routeIs('admin.reports') ? 'active' : '' }}" style="display:flex;align-items:center;gap:.65rem;" data-tooltip="Reports">
            <svg style="flex-shrink:0;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg>
            <span class="nav-label" style="flex:1;">Reports</span>
        </a>

        {{-- Inquiries --}}
        <a href="{{ route('admin.inquiries.index') }}" class="{{ request()->routeIs('admin.inquiries*') ? 'active' : '' }}" style="display:flex;align-items:center;gap:.65rem;" data-tooltip="Inquiries">
            <svg style="flex-shrink:0;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            <span class="nav-label" style="flex:1;">Inquiries</span>
            <span id="nav-badge-inquiry" class="nav-badge" style="background:#dc2626;color:#fff;border-radius:999px;font-size:.63rem;font-weight:700;min-width:18px;height:18px;display:{{ $newInquiryCount > 0 ? 'inline-flex' : 'none' }};align-items:center;justify-content:center;padding:0 4px;flex-shrink:0;">{{ $newInquiryCount ?: '' }}</span>
        </a>

        <div class="nav-divider" style="height:1px;background:#e9ecef;margin:.35rem 1.25rem;"></div>

        {{-- Users --}}
        <a href="{{ route('admin.users.index', ['role' => 'employee']) }}" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}" style="display:flex;align-items:center;gap:.65rem;" data-tooltip="Users">
            <svg style="flex-shrink:0;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
            <span class="nav-label" style="flex:1;">Users</span>
        </a>

        {{-- Services --}}
        <a href="{{ route('admin.services.index') }}" class="{{ request()->routeIs('admin.services*') ? 'active' : '' }}" style="display:flex;align-items:center;gap:.65rem;" data-tooltip="Services">
            <svg style="flex-shrink:0;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
            <span class="nav-label" style="flex:1;">Services</span>
        </a>

        {{-- Device Catalog --}}
        <a href="{{ route('admin.device-catalog.index') }}" class="{{ request()->routeIs('admin.device-catalog*') ? 'active' : '' }}" style="display:flex;align-items:center;gap:.65rem;" data-tooltip="Device Catalog">
            <svg style="flex-shrink:0;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            <span class="nav-label" style="flex:1;">Device Catalog</span>
        </a>

        {{-- Settings --}}
        <a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings*') ? 'active' : '' }}" style="display:flex;align-items:center;gap:.65rem;" data-tooltip="Settings">
            <svg style="flex-shrink:0;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
            <span class="nav-label" style="flex:1;">Settings</span>
        </a>

    </nav>

</aside>

<main class="main-content">
    <header class="topbar">
        <div id="topbar-title-slot" style="flex:1;min-width:0;overflow:hidden;">
            @stack('topbar-title')
        </div>
        <div style="display:flex;align-items:center;gap:.75rem;flex-shrink:0;">
            <div id="topbar-actions-slot" style="display:flex;align-items:center;gap:.75rem;">
                @stack('topbar-actions')
            </div>
            @include('admin.partials.clock')
        </div>
        <div class="portal-user-menu" id="portalUserMenu">
            <div class="portal-avatar" onclick="document.getElementById('portalDropdown').classList.toggle('open')" title="{{ $__u->name }}">
                @if($__photoPath && file_exists($__photoPath))
                    <img src="{{ route('users.photo', $__u) }}" alt="{{ $__u->name }}">
                @else
                    <span class="portal-avatar-initials">{{ $__initials }}</span>
                @endif
            </div>
            <div class="portal-dropdown" id="portalDropdown">
                <div class="portal-dropdown-header">
                    <div class="portal-dropdown-name">{{ $__u->name }}</div>
                    <div style="font-size:.75rem;color:#9ca3af;margin-top:.1rem;">Administrator</div>
                </div>
                <a href="{{ route('profile.edit') }}">My Account</a>
                <button type="button" onclick="openChpwModal()" style="display:block;width:100%;text-align:left;padding:.6rem 1rem;font-size:.875rem;color:#374151;background:none;border:none;cursor:pointer;transition:background .15s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='none'">Change Password</button>
                <hr>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            </div>
        </div>
    </header>
    <div class="content-body" style="background:#E8ECF0;">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        @yield('content')
    </div>
</main>

@include('layouts._change-password-modal')

<script>
(function () {
    var busy = false;

    // ── Active nav link ───────────────────────────────────────────────────────
    function updateNavActive(href) {
        var newPath = new URL(href, window.location.origin).pathname;
        var links   = document.querySelectorAll('.sidebar-nav > a[href]');
        var best = null, bestLen = 0;
        links.forEach(function (link) {
            var lPath = new URL(link.href, window.location.origin).pathname;
            if (newPath === lPath || newPath.startsWith(lPath + '/')) {
                if (lPath.length > bestLen) { bestLen = lPath.length; best = link; }
            }
        });
        links.forEach(function (link) { link.classList.remove('active'); });
        if (best) best.classList.add('active');
    }

    // ── Badge counts (lightweight JSON endpoint) ──────────────────────────────
    function setBadge(id, count) {
        var el = document.getElementById(id);
        if (!el) return;
        if (count > 0) { el.textContent = count; el.style.display = 'inline-flex'; }
        else           { el.style.display = 'none'; }
    }

    function refreshNavCounts() {
        fetch('{{ route("admin.nav-counts") }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (d) {
                if (!d) return;
                setBadge('nav-badge-wo',      d.wo);
                setBadge('nav-badge-invoice', d.invoice);
                setBadge('nav-badge-pending', d.pending);
                setBadge('nav-badge-inquiry', d.inquiry);
                setBadge('nav-badge-company', d.company);
            });
    }

    // Refresh badge counts every 60 seconds
    setInterval(refreshNavCounts, 60000);

    // ── Page navigation ───────────────────────────────────────────────────────
    function navigate(href, push) {
        if (busy) return;
        busy = true;

        var body = document.querySelector('.content-body');
        if (body) { body.style.transition = 'opacity .12s'; body.style.opacity = '.5'; }

        fetch(href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { if (!r.ok) throw r; return r.text(); })
            .then(function (html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');

                // If the destination uses a different layout (no .content-body), do a full load
                var nb = doc.querySelector('.content-body');
                if (!nb) { window.location.href = href; busy = false; return; }

                // Swap topbar slots first so scripts in new content can reference them
                var ntt = doc.getElementById('topbar-title-slot');
                var ctt = document.getElementById('topbar-title-slot');
                if (ntt && ctt) ctt.innerHTML = ntt.innerHTML;
                var nta = doc.getElementById('topbar-actions-slot');
                var cta = document.getElementById('topbar-actions-slot');
                if (nta && cta) cta.innerHTML = nta.innerHTML;

                // Swap content area
                if (body) {
                    body.innerHTML = nb.innerHTML;
                    body.style.opacity = '1';
                    // Re-execute any inline scripts in the new content
                    body.querySelectorAll('script').forEach(function (s) {
                        var n = document.createElement('script');
                        s.getAttributeNames().forEach(function (a) { n.setAttribute(a, s.getAttribute(a)); });
                        n.textContent = s.textContent;
                        s.replaceWith(n);
                    });
                }

                // Reset scroll position so the new content starts at the top
                if (body) body.scrollTop = 0;

                document.title = doc.title;
                if (push !== false) history.pushState(null, '', href);

                // Update active nav link in place and refresh badge counts
                updateNavActive(href);
                refreshNavCounts();

                busy = false;
            })
            .catch(function () { window.location.href = href; busy = false; });
    }

    window._adminNavigate = navigate;

    document.addEventListener('click', function (e) {
        // Keep user-menu dropdown close behaviour
        var menu = document.getElementById('portalUserMenu');
        var dd   = document.getElementById('portalDropdown');
        if (menu && dd && !menu.contains(e.target)) dd.classList.remove('open');

        // Clickable table rows
        var row = e.target.closest('tr[data-href]');
        if (row && !e.target.closest('a, button, form')) {
            e.preventDefault();
            navigate(row.dataset.href);
            return;
        }

        // Regular link clicks
        var link = e.target.closest('a[href]');
        if (!link || link.closest('form')) return;          // let logout forms through
        var href = link.getAttribute('href');
        if (!href || href[0] === '#' || /^(https?|mailto|tel):/.test(href)
                  || link.target || link.hasAttribute('download')) return;

        e.preventDefault();
        navigate(href);
    });

    // Handle browser back / forward
    window.addEventListener('popstate', function () {
        navigate(location.href, false);
    });
})();
</script>
@stack('scripts')
<script>
(function () {
    const sidebar = document.querySelector('.sidebar');
    const KEY = 'adminSidebarCollapsed';
    if (localStorage.getItem(KEY) === '1') sidebar.classList.add('collapsed');
    // Remove the pre-paint class so transitions work normally from here on
    requestAnimationFrame(function () {
        requestAnimationFrame(function () {
            document.documentElement.classList.remove('_sb-pre');
        });
    });
    window.toggleSidebar = function () {
        sidebar.classList.toggle('collapsed');
        localStorage.setItem(KEY, sidebar.classList.contains('collapsed') ? '1' : '0');
    };
})();
</script>
</body>
</html>
