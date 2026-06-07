<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="apple-touch-icon" sizes="512x512" href="/favicon-512.png">
    <title>Services – DataTel</title>
    <style>
        :root { --p: #1A3C5E; --a: #2E86C1; }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; color: #333; background: #E8ECF0; }

        /* ── Keyframes ─────────────────────────────────────────────── */
        @keyframes fade-up  { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes orb-float{ 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(24px,-18px) scale(1.04); } 66% { transform: translate(-14px,16px) scale(.97); } }
        @keyframes node-pulse { 0%,100% { opacity:.28; } 50% { opacity:.82; } }
        @keyframes line-fade  { 0%,100% { opacity:.12; } 50% { opacity:.48; } }
        @keyframes reveal-clip { from { clip-path: inset(0 100% 0 0); } to { clip-path: inset(0 0% 0 0); } }

        /* ── Page hero ─────────────────────────────────────────────── */
        .page-hero {
            background: var(--p);
            position: relative;
            overflow: hidden;
            padding: 3.75rem 2rem 3.5rem;
            text-align: center;
        }
        .orb {
            position: absolute; border-radius: 50%;
            filter: blur(82px); opacity: .15; pointer-events: none;
        }
        .orb-1 { width: 480px; height: 480px; background: var(--a);  top: -200px; left: -120px; animation: orb-float 20s ease-in-out infinite; }
        .orb-2 { width: 320px; height: 320px; background: #0c6ca5;   bottom: -120px; right: -80px; animation: orb-float 25s ease-in-out infinite reverse; }
        .orb-3 { width: 200px; height: 200px; background: #5ab8f5;   top: 20%; right: 25%; animation: orb-float 17s ease-in-out infinite 3s; }
        .hero-net { position: absolute; inset: 0; width: 100%; height: 100%; opacity: .08; pointer-events: none; }
        .page-hero-inner { position: relative; z-index: 2; max-width: 640px; margin: 0 auto; }

        .h-eyebrow {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(46,134,193,.2); color: #90d0f5;
            font-size: .71rem; font-weight: 700; letter-spacing: .14em;
            text-transform: uppercase; padding: .3rem .95rem;
            border-radius: 999px; border: 1px solid rgba(144,208,245,.18);
            margin-bottom: 1.2rem;
            animation: fade-up .55s ease both;
        }
        .page-hero h1 {
            font-size: 2.6rem; font-weight: 900; color: #fff;
            letter-spacing: -.5px; line-height: 1.1; margin-bottom: .8rem;
            animation: fade-up .55s .1s ease both;
        }
        .page-hero h1 span { color: #90d0f5; display: inline-block; animation: reveal-clip .7s .5s ease both; }
        .page-hero > .page-hero-inner > p {
            color: rgba(255,255,255,.62); font-size: 1rem; line-height: 1.75;
            animation: fade-up .55s .18s ease both;
        }

        /* ── Stats bar ─────────────────────────────────────────────── */
        .stats-bar { background: #fff; border-bottom: 1px solid #e5e9ee; }
        .stats-inner {
            max-width: 900px; margin: 0 auto;
            display: flex; align-items: stretch; justify-content: center; flex-wrap: wrap;
        }
        .stat {
            padding: 1.5rem 2.75rem; text-align: center;
            border-right: 1px solid #e5e9ee; flex: 1; min-width: 160px;
        }
        .stat:last-child { border-right: none; }
        .stat-num { font-size: 1.9rem; font-weight: 900; color: var(--p); line-height: 1; margin-bottom: .3rem; }
        .stat-num em { font-style: normal; color: var(--a); }
        .stat-label { font-size: .77rem; color: #6b7280; font-weight: 600; letter-spacing: .02em; text-transform: uppercase; }

        /* ── Services grid ─────────────────────────────────────────── */
        .content { max-width: 1180px; margin: 0 auto; padding: 4rem 2rem 5rem; }
        .svc-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1.5rem;
        }
        .svc-card {
            background: #fff; border-radius: 14px; overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            display: flex; flex-direction: column;
            transition: transform .3s cubic-bezier(.34,1.56,.64,1), box-shadow .3s;
        }
        body.js .svc-card { opacity: 0; transform: translateY(26px); transition: transform .5s cubic-bezier(.22,1,.36,1), box-shadow .3s, opacity .45s; }
        body.js .svc-card.in { opacity: 1; transform: translateY(0); }
        .svc-card:hover { transform: translateY(-5px) !important; box-shadow: 0 14px 36px rgba(26,60,94,.14); }
        .svc-img { width: 100%; height: 170px; object-fit: cover; display: block; }
        .svc-placeholder { width: 100%; height: 170px; background: linear-gradient(135deg, var(--p), var(--a)); display: flex; align-items: center; justify-content: center; }
        .svc-body { padding: 1.2rem 1.35rem 1.55rem; flex: 1; display: flex; flex-direction: column; }
        .svc-body h3 { color: var(--p); font-size: .98rem; font-weight: 700; margin-bottom: .42rem; }
        .svc-body p  { color: #6b7280; font-size: .84rem; line-height: 1.68; margin: 0; flex: 1; }

        /* ── CTA ────────────────────────────────────────────────────── */
        .cta { background: var(--p); padding: 5rem 2rem; text-align: center; position: relative; overflow: hidden; }
        .cta-inner { position: relative; z-index: 2; max-width: 680px; margin: 0 auto; }
        .cta-eyebrow {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(46,134,193,.2); color: #90d0f5;
            font-size: .71rem; font-weight: 700; letter-spacing: .13em;
            text-transform: uppercase; padding: .28rem .85rem;
            border-radius: 999px; margin-bottom: 1.15rem;
        }
        .cta h2 { color: #fff; font-size: 2rem; font-weight: 900; margin-bottom: .8rem; letter-spacing: -.3px; line-height: 1.15; }
        .cta > .cta-inner > p { color: rgba(255,255,255,.62); font-size: 1rem; line-height: 1.78; margin-bottom: 2rem; }
        .cta-btn {
            display: inline-flex; align-items: center; gap: .55rem;
            background: #fff; color: var(--p);
            text-decoration: none; font-size: .92rem; font-weight: 800;
            padding: .85rem 2rem; border-radius: 10px; letter-spacing: .02em;
            box-shadow: 0 4px 20px rgba(0,0,0,.22);
            transition: transform .18s, box-shadow .18s, background .18s;
            position: relative; overflow: hidden;
        }
        .cta-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,0,0,.3); background: #e8f4fd; }
        .cta-feats { display: flex; justify-content: center; gap: 2rem; margin-top: 2.25rem; flex-wrap: wrap; }
        .cta-feat { display: flex; align-items: center; gap: .45rem; color: rgba(255,255,255,.56); font-size: .8rem; }
        .cta-feat svg { color: var(--a); flex-shrink: 0; }

        /* ── Footer ────────────────────────────────────────────────── */
        footer { background: var(--p); color: rgba(255,255,255,.42); text-align: center; padding: 1.75rem 2rem; font-size: .82rem; }
        footer .foot-links { margin-top: .4rem; }
        footer a { color: rgba(255,255,255,.62); text-decoration: none; margin: 0 .5rem; }
        footer a:hover { color: #fff; }

        @media (max-width: 600px) { .stat { padding: 1.25rem 1.5rem; } .page-hero h1 { font-size: 2rem; } }
    </style>
</head>
<body>

@include('public._nav')

<section class="page-hero">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <svg class="hero-net" viewBox="0 0 1200 280" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
        <circle cx="80"  cy="40"  r="3.5" fill="white" style="animation:node-pulse 3s ease-in-out infinite 0s"/>
        <circle cx="280" cy="100" r="4.5" fill="white" style="animation:node-pulse 3s ease-in-out infinite .6s"/>
        <circle cx="500" cy="50"  r="3"   fill="white" style="animation:node-pulse 3s ease-in-out infinite 1.2s"/>
        <circle cx="720" cy="130" r="4"   fill="white" style="animation:node-pulse 3s ease-in-out infinite .3s"/>
        <circle cx="950" cy="60"  r="3.5" fill="white" style="animation:node-pulse 3s ease-in-out infinite 1.8s"/>
        <circle cx="1120" cy="140" r="3"  fill="white" style="animation:node-pulse 3s ease-in-out infinite .9s"/>
        <circle cx="400" cy="200" r="3.5" fill="white" style="animation:node-pulse 3s ease-in-out infinite 1.5s"/>
        <circle cx="830" cy="220" r="4"   fill="white" style="animation:node-pulse 3s ease-in-out infinite 2.1s"/>
        <line x1="80"  y1="40"  x2="280" y2="100" stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite .2s"/>
        <line x1="280" y1="100" x2="500" y2="50"  stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite .8s"/>
        <line x1="500" y1="50"  x2="720" y2="130" stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite .5s"/>
        <line x1="720" y1="130" x2="950" y2="60"  stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite 1.2s"/>
        <line x1="950" y1="60"  x2="1120" y2="140" stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite .4s"/>
        <line x1="280" y1="100" x2="400" y2="200" stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite 1.6s"/>
        <line x1="720" y1="130" x2="830" y2="220" stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite 1s"/>
        <line x1="400" y1="200" x2="830" y2="220" stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite 2s"/>
    </svg>

    <div class="page-hero-inner">
        <div class="h-eyebrow">
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
            Full-Service Solutions
        </div>
        <h1>Our <span>Services</span></h1>
        <p>Professional data communications and cabling solutions — designed, installed, and certified for businesses of every size.</p>
    </div>
</section>

<div class="stats-bar">
    <div class="stats-inner">
        <div class="stat">
            <div class="stat-num">{{ $services->count() }}<em>+</em></div>
            <div class="stat-label">Service Offerings</div>
        </div>
        <div class="stat">
            <div class="stat-num"><em>✓</em></div>
            <div class="stat-label">Licensed & Insured</div>
        </div>
        <div class="stat">
            <div class="stat-num">24<em>/7</em></div>
            <div class="stat-label">Emergency Support</div>
        </div>
        <div class="stat">
            <div class="stat-num"><em>100</em>%</div>
            <div class="stat-label">Certified Installs</div>
        </div>
    </div>
</div>

<div class="content">
    <div class="svc-grid">
        @foreach($services as $service)
        <div class="svc-card">
            @if($service->imageUrl())
                <img src="{{ $service->imageUrl() }}" alt="{{ $service->name }}" class="svc-img">
            @else
                <div class="svc-placeholder">
                    <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.3)" stroke-width="1.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2v-4M9 21H5a2 2 0 01-2-2v-4m0 0h18"/></svg>
                </div>
            @endif
            <div class="svc-body">
                <h3>{{ $service->name }}</h3>
                <p>{{ $service->description }}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>

<div class="cta">
    <div class="orb orb-1" style="top:-100px;left:10%;opacity:.1;"></div>
    <div class="orb orb-2" style="bottom:-100px;right:10%;opacity:.1;"></div>
    <div class="cta-inner">
        <div class="cta-eyebrow">
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Get Started Today
        </div>
        <h2>Ready to work with us?</h2>
        <p>Create a free customer account to submit service requests, track your work orders in real time, and manage invoices — all from one place.</p>
        <a href="{{ route('register') }}" class="cta-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Create a Customer Account
        </a>
        <div class="cta-feats">
            <div class="cta-feat"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Free to sign up</div>
            <div class="cta-feat"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Track jobs in real time</div>
            <div class="cta-feat"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> View &amp; manage invoices</div>
            <div class="cta-feat"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Digital job sign-off</div>
        </div>
    </div>
</div>

<footer>
    <div>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</div>
    <div class="foot-links">
        <a href="{{ route('services') }}">Services</a>
        <a href="{{ route('about') }}">About</a>
        <a href="{{ route('contact') }}">Contact</a>
    </div>
</footer>

<script>
document.body.classList.add('js');
(function () {
    var cards = document.querySelectorAll('.svc-card');
    if (!cards.length || !window.IntersectionObserver) { cards.forEach(function(c){ c.classList.add('in'); }); return; }
    var fired = false;
    var obs = new IntersectionObserver(function (entries) {
        if (fired || !entries[0].isIntersecting) return;
        fired = true;
        cards.forEach(function (card, i) { setTimeout(function () { card.classList.add('in'); }, i * 80); });
        obs.disconnect();
    }, { threshold: 0.06 });
    obs.observe(document.querySelector('.content'));
})();
</script>

</body>
</html>
