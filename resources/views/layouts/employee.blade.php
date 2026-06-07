<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="apple-touch-icon" sizes="512x512" href="/favicon-512.png">
    <title>@yield('title') – DataTel Employee Portal</title>
    @include('layouts.portal-styles')
</head>
<body class="portal-layout">

<header class="portal-header">
    <a href="{{ route('home') }}">
        <img src="{{ route('site.logo') }}" alt="DataTel">
    </a>
    <nav>
        <a href="{{ route('employee.calendar') }}" class="{{ request()->routeIs('employee.calendar') ? 'active' : '' }}">Calendar</a>
        <a href="#">My Work Orders</a>
        <a href="#">Clock In/Out</a>
        <a href="#">Time Card</a>
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
            <a href="{{ route('employee.account') }}" class="{{ request()->routeIs('employee.account') ? 'active' : '' }}">My Account</a>
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
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif
    @yield('content')
</main>

</body>
</html>
