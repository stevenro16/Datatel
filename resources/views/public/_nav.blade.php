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

/* Drawer wrapper — transparent on desktop, becomes the mobile dropdown panel */
.pub-nav-drawer { display: contents; }

/* Hamburger button — hidden on desktop, shown on mobile */
.pub-nav-burger {
    display: none;
    align-items: center;
    justify-content: center;
    background: none;
    border: none;
    cursor: pointer;
    padding: .35rem;
    margin-left: auto;
    color: #1A3C5E;
    line-height: 0;
}
.pub-nav-burger:hover { color: #2E86C1; }

@media (max-width: 768px) {
    .pub-nav { padding: 0 1rem; height: 60px; }
    .pub-nav-logo { margin-right: 0; }
    .pub-nav-logo img { height: 52px; }
    .pub-nav-burger { display: flex; }

    /* Hide drawer by default on mobile — toggled open by .pub-nav.open */
    .pub-nav-drawer {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border-bottom: 1px solid #dde3ea;
        box-shadow: 0 8px 20px rgba(26,60,94,.12);
        padding: .65rem 1rem .85rem;
    }
    .pub-nav.open .pub-nav-drawer {
        display: flex;
        flex-direction: column;
        align-items: stretch;
    }

    /* Stack nav links vertically in the drawer */
    .pub-nav-links {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 0;
        height: auto;
    }
    .pub-nav-link {
        height: auto;
        padding: .75rem .25rem;
        border-bottom: 1px solid #f0f0f0;
        font-size: .95rem;
    }
    .pub-nav-link::after { display: none; }

    /* Stack action buttons vertically, full width */
    .pub-nav-actions {
        flex-direction: column;
        align-items: stretch;
        gap: .55rem;
        margin: .75rem 0 0;
    }
    .pub-btn-register,
    .pub-btn-portal {
        justify-content: center;
        width: 100%;
        padding: .7rem 1rem;
    }
}
</style>

<nav class="pub-nav">
    <a href="{{ route('home') }}" class="pub-nav-logo">
        <img src="{{ route('site.logo') }}" alt="DataTel">
    </a>

    <div class="pub-nav-drawer">
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
    </div>

    <button type="button" class="pub-nav-burger" aria-label="Menu" aria-expanded="false" onclick="pubNavToggle(this)">
        <svg class="pub-nav-burger-open"  xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="4" y1="7"  x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/></svg>
        <svg class="pub-nav-burger-close" xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" style="display:none;"><line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/></svg>
    </button>
</nav>

<script>
function pubNavToggle(btn) {
    var nav = btn.closest('.pub-nav');
    var open = nav.classList.toggle('open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    btn.querySelector('.pub-nav-burger-open').style.display  = open ? 'none' : '';
    btn.querySelector('.pub-nav-burger-close').style.display = open ? '' : 'none';
}
document.addEventListener('click', function (e) {
    var nav = document.querySelector('.pub-nav.open');
    if (!nav) return;
    // Close after tapping a link or action button inside the drawer (so navigation proceeds with menu closed)
    if (e.target.closest('.pub-nav-drawer a, .pub-nav-drawer button')) {
        var burger = nav.querySelector('.pub-nav-burger');
        if (burger) pubNavToggle(burger);
        return;
    }
    // Close when tapping outside the nav entirely
    if (!nav.contains(e.target)) {
        var burger2 = nav.querySelector('.pub-nav-burger');
        if (burger2) pubNavToggle(burger2);
    }
});
</script>
