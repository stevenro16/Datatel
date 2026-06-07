<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="apple-touch-icon" sizes="512x512" href="/favicon-512.png">
    <title>@yield('title') – DataTel Customer Portal</title>
    @include('layouts.portal-styles')
</head>
<body class="portal-layout">

<header class="portal-header">
    <a href="{{ route('home') }}">
        <img src="{{ route('site.logo') }}" alt="DataTel">
    </a>
    @php
        $__cMembership = \App\Models\CompanyMember::where('user_id', auth()->id())
            ->whereIn('status', ['pending','active'])->with('company')->latest()->first();
        $__companyPending = $__cMembership && $__cMembership->status === 'pending';
        $__companyBadge   = (!$__companyPending && $__cMembership)
            ? \App\Models\CompanyMember::where('company_id', $__cMembership->company_id)->where('status','pending')->count()
            : 0;
    @endphp
    <nav>
        <a href="{{ route('portal.work-orders.index') }}" class="{{ request()->routeIs('portal.work-orders*') ? 'active' : '' }}">Work Orders</a>
        <a href="{{ route('portal.invoices.index') }}" class="{{ request()->routeIs('portal.invoices*') ? 'active' : '' }}">Invoices</a>
        <a href="{{ route('portal.company') }}" class="{{ request()->routeIs('portal.company*') ? 'active' : '' }}"
           style="position:relative;display:inline-flex;align-items:center;gap:.35rem;">
            Company
            @if($__companyPending)
            <span style="width:7px;height:7px;border-radius:50%;background:#f59e0b;flex-shrink:0;" title="Pending request"></span>
            @elseif($__companyBadge > 0)
            <span style="background:#dc2626;color:#fff;border-radius:999px;font-size:.6rem;font-weight:700;min-width:16px;height:16px;display:inline-flex;align-items:center;justify-content:center;padding:0 3px;">{{ $__companyBadge }}</span>
            @endif
        </a>
        <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile*') ? 'active' : '' }}">Account</a>
    </nav>
    @php
        $__u = auth()->user();
        $__initials = collect(explode(' ', $__u->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
        $__photoPath = $__u->profile_photo ? storage_path('app/profile-photos/'.$__u->profile_photo) : null;
    @endphp
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
<script>
document.addEventListener('click', function(e) {
    const menu = document.getElementById('portalUserMenu');
    const dd   = document.getElementById('portalDropdown');
    if (menu && dd && !menu.contains(e.target)) dd.classList.remove('open');
});
</script>

@include('layouts._change-password-modal')

<main class="portal-content">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif
    @yield('content')
</main>

<script>
document.addEventListener('click', function(e) {
    const row = e.target.closest('tr[data-href]');
    if (row && !e.target.closest('a, button, form')) {
        window.location.href = row.dataset.href;
    }
});
</script>

</body>
</html>
