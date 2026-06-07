<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="apple-touch-icon" sizes="512x512" href="/favicon-512.png">
    <title>DataTel – Data Communications & Cabling</title>
    <style>
        :root { --p: #1A3C5E; --a: #2E86C1; }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; color: #333; background: #E8ECF0; }

        /* ── Keyframes ─────────────────────────────────────────────────── */
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(22px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes slide-in-right {
            from { opacity: 0; transform: translateX(38px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes orb-float {
            0%,100% { transform: translate(0,0) scale(1); }
            33%      { transform: translate(26px,-20px) scale(1.04); }
            66%      { transform: translate(-16px,18px) scale(.97); }
        }
        @keyframes node-pulse {
            0%,100% { opacity:.3; }
            50%      { opacity:.9; }
        }
        @keyframes line-fade {
            0%,100% { opacity:.15; }
            50%      { opacity:.55; }
        }
        @keyframes icon-pop {
            from { opacity:0; transform:scale(.55); }
            to   { opacity:1; transform:scale(1); }
        }
        @keyframes card-breathe {
            0%,100% { box-shadow: 0 8px 40px rgba(26,60,94,.14), 0 2px 8px rgba(26,60,94,.08); }
            50%      { box-shadow: 0 8px 48px rgba(46,134,193,.22), 0 2px 12px rgba(26,60,94,.1), 0 0 0 1px rgba(46,134,193,.08); }
        }
        @keyframes reveal-clip {
            from { clip-path: inset(0 100% 0 0); }
            to   { clip-path: inset(0 0% 0 0); }
        }

        /* ── Hero ──────────────────────────────────────────────────────── */
        .hero {
            display: grid;
            grid-template-columns: 1fr 460px;
            min-height: calc(100vh - 88px);
        }

        /* Left — dark branded panel */
        .hero-left {
            background: var(--p);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem 4.5rem 4rem 5rem;
            overflow: hidden;
        }

        /* Animated blobs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(88px);
            opacity: .16;
            pointer-events: none;
        }
        .orb-1 { width:560px; height:560px; background:var(--a);  top:-180px; left:-120px; animation:orb-float 20s ease-in-out infinite; }
        .orb-2 { width:360px; height:360px; background:#0c6ca5;   bottom:-80px; right:-60px; animation:orb-float 24s ease-in-out infinite reverse; }
        .orb-3 { width:210px; height:210px; background:#5ab8f5;   top:55%; left:65%; animation:orb-float 16s ease-in-out infinite 4s; }

        /* Network topology SVG */
        .hero-net {
            position: absolute;
            inset: 0;
            width: 100%; height: 100%;
            opacity: .09;
            pointer-events: none;
        }

        /* Copy */
        .hero-copy { position: relative; z-index: 2; }

        .h-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            background: rgba(46,134,193,.2);
            color: #90d0f5;
            font-size: .71rem;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            padding: .32rem .95rem;
            border-radius: 999px;
            border: 1px solid rgba(144,208,245,.18);
            margin-bottom: 1.55rem;
            animation: fade-up .6s ease both;
        }

        .hero-title {
            font-size: 3.1rem;
            font-weight: 900;
            color: #fff;
            line-height: 1.1;
            letter-spacing: -.6px;
            margin-bottom: 1.2rem;
            animation: fade-up .6s .1s ease both;
        }
        .hero-title span {
            display: block;
            color: #90d0f5;
            animation: reveal-clip .75s .55s ease both;
        }

        .hero-sub {
            font-size: 1rem;
            color: rgba(255,255,255,.6);
            line-height: 1.8;
            max-width: 465px;
            margin-bottom: 2.4rem;
            animation: fade-up .6s .2s ease both;
        }

        .trust-pills {
            display: flex;
            flex-wrap: wrap;
            gap: .55rem;
            animation: fade-up .6s .3s ease both;
        }
        .trust-pill {
            display: inline-flex;
            align-items: center;
            gap: .38rem;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 9px;
            padding: .44rem .78rem;
            color: rgba(255,255,255,.8);
            font-size: .77rem;
            font-weight: 600;
            backdrop-filter: blur(3px);
            transition: background .2s, border-color .2s;
        }
        .trust-pill:hover { background: rgba(255,255,255,.14); border-color: rgba(255,255,255,.24); }
        .trust-pill svg { color: #90d0f5; flex-shrink: 0; }

        /* Right — login panel */
        .hero-right {
            background: #eef2f7;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 2.25rem;
        }

        /* Login card */
        .login-card {
            background: #fff;
            border-radius: 18px;
            padding: 2.2rem 2rem 1.8rem;
            width: 100%;
            max-width: 356px;
            animation: slide-in-right .65s .06s cubic-bezier(.22,1,.36,1) both,
                       card-breathe 7s 2.5s ease-in-out infinite;
        }

        .card-head { text-align: center; margin-bottom: 1.65rem; }

        .card-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px; height: 50px;
            background: linear-gradient(135deg, var(--p), var(--a));
            border-radius: 13px;
            margin-bottom: .85rem;
            box-shadow: 0 4px 16px rgba(46,134,193,.3);
            animation: icon-pop .5s .55s cubic-bezier(.34,1.56,.64,1) both;
        }
        .card-title { font-size: 1.14rem; font-weight: 800; color: var(--p); margin-bottom: .2rem; }
        .card-sub   { font-size: .8rem; color: #9ca3af; }

        /* Form fields */
        .f-group { margin-bottom: .92rem; }
        .f-group label {
            display: block;
            font-size: .75rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: .36rem;
            letter-spacing: .01em;
        }
        .f-group input[type="email"],
        .f-group input[type="password"],
        .f-group input[type="text"] {
            width: 100%;
            padding: .63rem .82rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: .88rem;
            font-family: inherit;
            color: #111;
            background: #fafafa;
            outline: none;
            transition: border-color .18s, box-shadow .18s, background .18s;
        }
        .f-group input:focus {
            border-color: var(--a);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(46,134,193,.13);
        }

        .pw-wrap { position: relative; }
        .pw-wrap input { padding-right: 2.6rem; }
        .pw-eye {
            position: absolute;
            right: .65rem; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            cursor: pointer;
            color: #9ca3af;
            padding: 0; line-height: 0;
            transition: color .15s;
        }
        .pw-eye:hover { color: var(--p); }

        .row-mid {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: .38rem 0 1.1rem;
        }
        .chk-label {
            display: flex; align-items: center; gap: .38rem;
            font-size: .79rem; color: #555; cursor: pointer; user-select: none;
        }
        .chk-label input[type="checkbox"] {
            width: 14px; height: 14px;
            accent-color: var(--a); cursor: pointer;
        }
        .link-sm { font-size: .76rem; color: var(--a); text-decoration: none; font-weight: 600; }
        .link-sm:hover { text-decoration: underline; }

        /* Submit button */
        .btn-submit {
            width: 100%;
            padding: .78rem;
            border: none;
            border-radius: 9px;
            background: linear-gradient(105deg, var(--p) 0%, var(--a) 100%);
            color: #fff;
            font-size: .92rem;
            font-weight: 700;
            letter-spacing: .025em;
            font-family: inherit;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(46,134,193,.35);
            transition: transform .18s, box-shadow .18s;
            display: block;
            text-align: center;
            text-decoration: none;
        }
        .btn-submit::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.18), transparent);
            background-size: 200% 100%;
            background-position: -200% center;
            transition: background-position .55s;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(46,134,193,.44); }
        .btn-submit:hover::after { background-position: 200% center; }
        .btn-submit:active { transform: none; }

        .card-foot {
            margin-top: 1.15rem;
            padding-top: .95rem;
            border-top: 1px solid #f1f3f5;
            text-align: center;
            font-size: .79rem;
            color: #9ca3af;
        }
        .card-foot a { color: var(--a); font-weight: 600; text-decoration: none; }
        .card-foot a:hover { text-decoration: underline; }

        .alert-err {
            background: #fee2e2; color: #991b1b;
            padding: .58rem .82rem; border-radius: 7px;
            margin-bottom: .85rem; font-size: .81rem;
            border: 1px solid #fecaca;
        }
        .status-ok {
            background: #dcfce7; color: #166534;
            padding: .58rem .82rem; border-radius: 7px;
            margin-bottom: .85rem; font-size: .81rem;
            border: 1px solid #bbf7d0;
        }

        /* ── Services ──────────────────────────────────────────────────── */
        .services-section { padding: 5.5rem 2rem 6rem; }
        .services-inner { max-width: 1180px; margin: 0 auto; }

        .sec-eyebrow {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(46,134,193,.1); color: var(--a);
            font-size: .71rem; font-weight: 700; letter-spacing: .13em;
            text-transform: uppercase; padding: .28rem .85rem;
            border-radius: 999px; margin-bottom: .8rem;
        }
        .sec-title {
            font-size: 2.5rem; font-weight: 900; color: var(--p);
            letter-spacing: -.4px; line-height: 1.1; margin-bottom: .7rem;
        }
        .sec-desc {
            color: #6b7280; font-size: .97rem; line-height: 1.75;
            max-width: 560px; margin-bottom: 3rem;
        }
        .svc-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(258px,1fr));
            gap: 1.5rem;
        }
        .svc-card {
            background: #fff; border-radius: 14px; overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            display: flex; flex-direction: column;
            transition: transform .3s cubic-bezier(.34,1.56,.64,1), box-shadow .3s;
        }
        body.js .svc-card {
            opacity: 0; transform: translateY(26px);
            transition: transform .5s cubic-bezier(.22,1,.36,1), box-shadow .3s, opacity .45s;
        }
        body.js .svc-card.in { opacity: 1; transform: translateY(0); }
        .svc-card:hover { transform: translateY(-5px) !important; box-shadow: 0 14px 36px rgba(26,60,94,.14); }
        .svc-img { width: 100%; height: 168px; object-fit: cover; display: block; }
        .svc-placeholder {
            width: 100%; height: 168px;
            background: linear-gradient(135deg, var(--p), var(--a));
            display: flex; align-items: center; justify-content: center;
        }
        .svc-body { padding: 1.2rem 1.35rem 1.5rem; flex: 1; display: flex; flex-direction: column; }
        .svc-body h3 { color: var(--p); font-size: .98rem; font-weight: 700; margin-bottom: .42rem; }
        .svc-body p  { color: #6b7280; font-size: .84rem; line-height: 1.68; margin: 0; flex: 1; }

        /* ── Footer ────────────────────────────────────────────────────── */
        footer {
            background: var(--p);
            color: rgba(255,255,255,.42);
            text-align: center;
            padding: 1.75rem 2rem;
            font-size: .82rem;
        }
        footer .foot-links { margin-top: .4rem; }
        footer a { color: rgba(255,255,255,.62); text-decoration: none; margin: 0 .5rem; }
        footer a:hover { color: #fff; }

        /* ── Responsive ────────────────────────────────────────────────── */
        @media (max-width: 860px) {
            .hero { grid-template-columns: 1fr; min-height: auto; display: flex; flex-direction: column; }
            .hero-right { order: -1; padding: 2rem 1.5rem 1.5rem; background: #E8ECF0; }
            .login-card { max-width: 100%; }
            .hero-left { padding: 3rem 2rem 3.5rem; }
            .hero-title { font-size: 2.25rem; }
        }
    </style>
</head>
<body>

@include('public._nav')

<section class="hero">

    {{-- ── Left: Brand copy ─────────────────────────────────────────── --}}
    <div class="hero-left">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>

        {{-- Animated network topology --}}
        <svg class="hero-net" viewBox="0 0 860 600" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
            <circle cx="90"  cy="70"  r="4.5" fill="white" style="animation:node-pulse 3.2s ease-in-out infinite 0s"/>
            <circle cx="310" cy="130" r="5.5" fill="white" style="animation:node-pulse 3.2s ease-in-out infinite .7s"/>
            <circle cx="560" cy="80"  r="3.5" fill="white" style="animation:node-pulse 3.2s ease-in-out infinite 1.3s"/>
            <circle cx="720" cy="240" r="4.5" fill="white" style="animation:node-pulse 3.2s ease-in-out infinite .4s"/>
            <circle cx="480" cy="320" r="5"   fill="white" style="animation:node-pulse 3.2s ease-in-out infinite 1.8s"/>
            <circle cx="190" cy="300" r="3.5" fill="white" style="animation:node-pulse 3.2s ease-in-out infinite 1s"/>
            <circle cx="70"  cy="460" r="4"   fill="white" style="animation:node-pulse 3.2s ease-in-out infinite 2.2s"/>
            <circle cx="580" cy="500" r="3.5" fill="white" style="animation:node-pulse 3.2s ease-in-out infinite .6s"/>
            <circle cx="760" cy="480" r="4"   fill="white" style="animation:node-pulse 3.2s ease-in-out infinite 1.6s"/>
            <circle cx="350" cy="490" r="3"   fill="white" style="animation:node-pulse 3.2s ease-in-out infinite 2.5s"/>
            <line x1="90"  y1="70"  x2="310" y2="130" stroke="white" stroke-width=".9" style="animation:line-fade 4s ease-in-out infinite .2s"/>
            <line x1="310" y1="130" x2="560" y2="80"  stroke="white" stroke-width=".9" style="animation:line-fade 4s ease-in-out infinite .9s"/>
            <line x1="560" y1="80"  x2="720" y2="240" stroke="white" stroke-width=".9" style="animation:line-fade 4s ease-in-out infinite .5s"/>
            <line x1="720" y1="240" x2="480" y2="320" stroke="white" stroke-width=".9" style="animation:line-fade 4s ease-in-out infinite 1.3s"/>
            <line x1="480" y1="320" x2="190" y2="300" stroke="white" stroke-width=".9" style="animation:line-fade 4s ease-in-out infinite 1.9s"/>
            <line x1="190" y1="300" x2="90"  y2="70"  stroke="white" stroke-width=".9" style="animation:line-fade 4s ease-in-out infinite .7s"/>
            <line x1="190" y1="300" x2="70"  y2="460" stroke="white" stroke-width=".9" style="animation:line-fade 4s ease-in-out infinite 1.1s"/>
            <line x1="480" y1="320" x2="350" y2="490" stroke="white" stroke-width=".9" style="animation:line-fade 4s ease-in-out infinite 2s"/>
            <line x1="350" y1="490" x2="580" y2="500" stroke="white" stroke-width=".9" style="animation:line-fade 4s ease-in-out infinite 1.7s"/>
            <line x1="580" y1="500" x2="760" y2="480" stroke="white" stroke-width=".9" style="animation:line-fade 4s ease-in-out infinite .3s"/>
            <line x1="720" y1="240" x2="760" y2="480" stroke="white" stroke-width=".9" style="animation:line-fade 4s ease-in-out infinite 2.1s"/>
            <line x1="310" y1="130" x2="480" y2="320" stroke="white" stroke-width=".9" style="animation:line-fade 4s ease-in-out infinite .6s"/>
            <line x1="70"  y1="460" x2="350" y2="490" stroke="white" stroke-width=".9" style="animation:line-fade 4s ease-in-out infinite 1.5s"/>
        </svg>

        <div class="hero-copy">
            <div class="h-eyebrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                Professional Service Provider
            </div>
            <h1 class="hero-title">
                Your Network,
                <span>Our Expertise</span>
            </h1>
            <p class="hero-sub">
                DataTel delivers enterprise-grade data communications and structured cabling — fiber optic, IP telephony, and complete network infrastructure for businesses across the region.
            </p>
            <div class="trust-pills">
                <div class="trust-pill">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Licensed & Insured
                </div>
                <div class="trust-pill">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Rapid Response
                </div>
                <div class="trust-pill">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
                    Structured Cabling
                </div>
                <div class="trust-pill">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                    Fiber & Wireless
                </div>
            </div>
        </div>
    </div>

    {{-- ── Right: Login card ─────────────────────────────────────────── --}}
    <div class="hero-right">
        <div class="login-card">

            @auth
            {{-- Already signed in --}}
            <div style="text-align:center;padding:.5rem 0 .25rem;">
                <div class="card-icon" style="margin:0 auto .9rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div class="card-title" style="margin-bottom:.28rem;">Welcome back</div>
                <div class="card-sub" style="margin-bottom:1.6rem;">{{ auth()->user()->name }}</div>
                @php
                    $dest = match(auth()->user()->role) {
                        'admin'    => route('admin.dashboard'),
                        'employee' => route('employee.calendar'),
                        default    => route('portal.work-orders.index'),
                    };
                @endphp
                <a href="{{ $dest }}" class="btn-submit" style="padding:.82rem;">Go to my portal →</a>
            </div>

            @else
            {{-- Login form --}}
            <div class="card-head">
                <div class="card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div class="card-title">Client Portal</div>
                <div class="card-sub">Sign in to your account</div>
            </div>

            @if(session('status'))
            <div class="status-ok">{{ session('status') }}</div>
            @endif

            @if($errors->any())
            <div class="alert-err">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="f-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}"
                           required autofocus autocomplete="username"
                           placeholder="you@company.com">
                </div>

                <div class="f-group">
                    <label for="password">Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="password" name="password"
                               required autocomplete="current-password"
                               placeholder="••••••••">
                        <button type="button" class="pw-eye" onclick="togglePw()"
                                tabindex="-1" title="Show / hide password">
                            <svg id="eye-on" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="eye-off" style="display:none" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>

                <div class="row-mid">
                    <label class="chk-label">
                        <input type="checkbox" name="remember" id="remember_me">
                        Remember me
                    </label>
                    @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="link-sm">Forgot password?</a>
                    @endif
                </div>

                <button type="submit" class="btn-submit">Sign In</button>
            </form>

            <div class="card-foot">
                New to DataTel? <a href="{{ route('register') }}">Create an account →</a>
            </div>
            @endauth

        </div>
    </div>
</section>

{{-- ── Services ───────────────────────────────────────────────────────────── --}}
<section class="services-section">
    <div class="services-inner">
        <div class="sec-eyebrow">
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            What We Do
        </div>
        <h2 class="sec-title">Enterprise-Grade Solutions</h2>
        <p class="sec-desc">From structured cabling to fiber optic infrastructure — we design, install, and support the connectivity your business depends on.</p>
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
</section>

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

function togglePw() {
    var inp = document.getElementById('password');
    var show = inp.type === 'password';
    inp.type = show ? 'text' : 'password';
    document.getElementById('eye-on').style.display  = show ? 'none' : '';
    document.getElementById('eye-off').style.display = show ? ''     : 'none';
}

// Scroll-reveal service cards with stagger
(function () {
    var cards = document.querySelectorAll('.svc-card');
    if (!cards.length) return;
    if (!window.IntersectionObserver) {
        cards.forEach(function (c) { c.classList.add('in'); });
        return;
    }
    var section = document.querySelector('.services-section');
    var fired   = false;
    var obs = new IntersectionObserver(function (entries) {
        if (fired || !entries[0].isIntersecting) return;
        fired = true;
        cards.forEach(function (card, i) {
            setTimeout(function () { card.classList.add('in'); }, i * 88);
        });
        obs.disconnect();
    }, { threshold: 0.07 });
    obs.observe(section);
})();
</script>

</body>
</html>
