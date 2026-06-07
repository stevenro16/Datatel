{{-- Shared public navigation --}}
<style>
.pub-nav {
    background: #fff;
    border-bottom: 1px solid #dde3ea;
    box-shadow: 0 2px 16px rgba(26,60,94,.07);
    padding: 0 2.5rem;
    height: 88px;
    overflow: visible;
    display: flex;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 500;
}

.pub-nav-logo {
    display: block;
    line-height: 0;
    flex-shrink: 0;
    margin-right: 2.25rem;
}
.pub-nav-logo img {
    height: 116px;
    width: auto;
    display: block;
}

.pub-nav-links {
    display: flex;
    align-items: center;
    gap: .25rem;
    height: 100%;
}

.pub-nav-link {
    position: relative;
    display: inline-flex;
    align-items: center;
    height: 100%;
    padding: 0 .9rem;
    color: #1A3C5E;
    text-decoration: none;
    font-size: .88rem;
    font-weight: 600;
    letter-spacing: .02em;
    transition: color .18s;
}
.pub-nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: .9rem;
    right: .9rem;
    height: 3px;
    background: #2E86C1;
    border-radius: 3px 3px 0 0;
    transform: scaleX(0);
    transform-origin: center;
    transition: transform .2s cubic-bezier(.4,0,.2,1);
}
.pub-nav-link:hover          { color: #2E86C1; }
.pub-nav-link:hover::after   { transform: scaleX(1); }
.pub-nav-link.active         { color: #2E86C1; }
.pub-nav-link.active::after  { transform: scaleX(1); }

.pub-nav-actions {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: .65rem;
}

/* "Create Account" outlined button */
.pub-btn-register {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .55rem 1.15rem;
    border: 2px solid #1A3C5E;
    border-radius: 8px;
    color: #1A3C5E;
    background: transparent;
    font-size: .83rem;
    font-weight: 700;
    text-decoration: none;
    letter-spacing: .02em;
    transition: background .18s, color .18s, border-color .18s, box-shadow .18s;
    white-space: nowrap;
}
.pub-btn-register:hover {
    background: #1A3C5E;
    color: #fff;
    box-shadow: 0 4px 12px rgba(26,60,94,.22);
}
.pub-btn-register svg { flex-shrink: 0; }

/* Login / portal solid button */
.pub-btn-portal {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .55rem 1.25rem;
    background: #2E86C1;
    color: #fff !important;
    border: 2px solid #2E86C1;
    border-radius: 8px;
    font-size: .83rem;
    font-weight: 700;
    text-decoration: none !important;
    letter-spacing: .02em;
    cursor: pointer;
    font-family: inherit;
    transition: background .18s, border-color .18s, box-shadow .18s, transform .15s;
    white-space: nowrap;
}
.pub-btn-portal:hover {
    background: #1A3C5E;
    border-color: #1A3C5E;
    box-shadow: 0 4px 14px rgba(46,134,193,.32);
    transform: translateY(-1px);
}
.pub-btn-portal svg { flex-shrink: 0; }
</style>

<nav class="pub-nav">
    <a href="{{ route('home') }}" class="pub-nav-logo">
        <img src="{{ route('site.logo') }}" alt="DataTel">
    </a>

    <div class="pub-nav-links">
        <a href="{{ route('services') }}" class="pub-nav-link {{ request()->routeIs('services') ? 'active' : '' }}">Services</a>
        <a href="{{ route('about') }}"    class="pub-nav-link {{ request()->routeIs('about')    ? 'active' : '' }}">About</a>
        <a href="{{ route('contact') }}"  class="pub-nav-link {{ request()->routeIs('contact')  ? 'active' : '' }}">Contact</a>
    </div>

    <div class="pub-nav-actions">
        @guest
        <a href="{{ route('register') }}" class="pub-btn-register">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Create Account
        </a>
        @endguest
        @include('public._nav-auth')
    </div>
</nav>
