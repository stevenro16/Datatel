<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
    <link rel="apple-touch-icon" sizes="512x512" href="/favicon-512.png">
    <title>About – DataTel</title>
    <style>
        :root { --p: #1A3C5E; --a: #2E86C1; }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; color: #333; background: #E8ECF0; }

        /* ── Keyframes ─────────────────────────────────────────────── */
        @keyframes fade-up   { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes orb-float { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(24px,-18px) scale(1.04); } 66% { transform: translate(-14px,16px) scale(.97); } }
        @keyframes node-pulse { 0%,100% { opacity:.28; } 50% { opacity:.82; } }
        @keyframes line-fade  { 0%,100% { opacity:.12; } 50% { opacity:.48; } }
        @keyframes reveal-clip { from { clip-path: inset(0 100% 0 0); } to { clip-path: inset(0 0% 0 0); } }

        /* ── Page hero ─────────────────────────────────────────────── */
        .page-hero { background: var(--p); position: relative; overflow: hidden; padding: 3.75rem 2rem 3.5rem; text-align: center; }
        .orb { position: absolute; border-radius: 50%; filter: blur(82px); opacity: .15; pointer-events: none; }
        .orb-1 { width: 480px; height: 480px; background: var(--a);  top: -200px; left: -100px; animation: orb-float 20s ease-in-out infinite; }
        .orb-2 { width: 300px; height: 300px; background: #0c6ca5;   bottom: -120px; right: -60px; animation: orb-float 25s ease-in-out infinite reverse; }
        .orb-3 { width: 180px; height: 180px; background: #5ab8f5;   top: 30%; right: 22%; animation: orb-float 17s ease-in-out infinite 3s; }
        .hero-net { position: absolute; inset: 0; width: 100%; height: 100%; opacity: .08; pointer-events: none; }
        .page-hero-inner { position: relative; z-index: 2; max-width: 640px; margin: 0 auto; }
        .h-eyebrow {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(46,134,193,.2); color: #90d0f5;
            font-size: .71rem; font-weight: 700; letter-spacing: .14em;
            text-transform: uppercase; padding: .3rem .95rem;
            border-radius: 999px; border: 1px solid rgba(144,208,245,.18);
            margin-bottom: 1.2rem; animation: fade-up .55s ease both;
        }
        .page-hero h1 {
            font-size: 2.6rem; font-weight: 900; color: #fff;
            letter-spacing: -.5px; line-height: 1.1; margin-bottom: .8rem;
            animation: fade-up .55s .1s ease both;
        }
        .page-hero h1 span { color: #90d0f5; display: inline-block; animation: reveal-clip .7s .5s ease both; }
        .page-hero-inner > p { color: rgba(255,255,255,.62); font-size: 1rem; line-height: 1.75; animation: fade-up .55s .18s ease both; }

        /* ── Content wrapper ───────────────────────────────────────── */
        .page-body { max-width: 1100px; margin: 0 auto; padding: 4rem 2rem 5rem; }

        /* ── Who we are ────────────────────────────────────────────── */
        .intro-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
            margin-bottom: 4.5rem;
        }
        .intro-text .sec-eyebrow {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(46,134,193,.1); color: var(--a);
            font-size: .71rem; font-weight: 700; letter-spacing: .13em;
            text-transform: uppercase; padding: .28rem .85rem;
            border-radius: 999px; margin-bottom: .85rem;
        }
        .intro-text h2 { font-size: 2rem; font-weight: 900; color: var(--p); letter-spacing: -.3px; line-height: 1.15; margin-bottom: .85rem; }
        .intro-text p { color: #555; font-size: .97rem; line-height: 1.82; margin-bottom: .9rem; }
        .intro-text p:last-child { margin-bottom: 0; }
        .intro-text a { color: var(--a); font-weight: 600; text-decoration: none; }
        .intro-text a:hover { text-decoration: underline; }

        .intro-visual {
            background: linear-gradient(135deg, var(--p) 0%, var(--a) 100%);
            border-radius: 18px;
            padding: 2.5rem 2rem;
            color: #fff;
            box-shadow: 0 12px 40px rgba(26,60,94,.2);
        }
        .mission-label { font-size: .72rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: rgba(255,255,255,.55); margin-bottom: .85rem; }
        .mission-text { font-size: 1.12rem; font-weight: 700; line-height: 1.65; color: rgba(255,255,255,.95); }
        .mission-divider { border: none; border-top: 1px solid rgba(255,255,255,.15); margin: 1.5rem 0; }
        .check-list { list-style: none; padding: 0; }
        .check-list li {
            display: flex; align-items: flex-start; gap: .6rem;
            font-size: .88rem; color: rgba(255,255,255,.8);
            margin-bottom: .65rem;
        }
        .check-list li:last-child { margin-bottom: 0; }
        .check-list svg { color: #90d0f5; flex-shrink: 0; margin-top: 1px; }

        /* ── Values grid ───────────────────────────────────────────── */
        .section-block { margin-bottom: 4.5rem; }
        .section-block .sec-eyebrow {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(46,134,193,.1); color: var(--a);
            font-size: .71rem; font-weight: 700; letter-spacing: .13em;
            text-transform: uppercase; padding: .28rem .85rem;
            border-radius: 999px; margin-bottom: .85rem;
        }
        .section-block h2 { font-size: 2rem; font-weight: 900; color: var(--p); letter-spacing: -.3px; line-height: 1.15; margin-bottom: .7rem; }
        .section-block > p { color: #6b7280; font-size: .97rem; line-height: 1.75; max-width: 600px; margin-bottom: 2.25rem; }

        .values-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 1.25rem; }
        .value-card {
            background: #fff; border-radius: 14px;
            padding: 1.6rem 1.4rem; display: flex; flex-direction: column; gap: .85rem;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            border-top: 3px solid var(--a);
            transition: transform .28s cubic-bezier(.34,1.56,.64,1), box-shadow .28s;
        }
        body.js .value-card { opacity: 0; transform: translateY(22px); transition: transform .5s cubic-bezier(.22,1,.36,1), box-shadow .28s, opacity .4s; }
        body.js .value-card.in { opacity: 1; transform: translateY(0); }
        .value-card:hover { transform: translateY(-4px) !important; box-shadow: 0 10px 28px rgba(26,60,94,.13); }
        .value-icon {
            width: 42px; height: 42px;
            background: rgba(46,134,193,.1);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .value-icon svg { color: var(--a); }
        .value-card h4 { font-size: .98rem; font-weight: 800; color: var(--p); margin: 0; }
        .value-card p  { font-size: .84rem; color: #6b7280; line-height: 1.65; margin: 0; }

        /* ── Why choose us ─────────────────────────────────────────── */
        .why-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 1rem; }
        .why-item {
            background: #fff; border-radius: 12px; padding: 1.25rem 1.4rem;
            display: flex; align-items: flex-start; gap: .9rem;
            box-shadow: 0 2px 6px rgba(0,0,0,.05);
            transition: transform .25s cubic-bezier(.34,1.56,.64,1), box-shadow .25s;
        }
        body.js .why-item { opacity: 0; transform: translateY(18px); transition: transform .45s cubic-bezier(.22,1,.36,1), box-shadow .25s, opacity .4s; }
        body.js .why-item.in { opacity: 1; transform: translateY(0); }
        .why-item:hover { transform: translateY(-3px) !important; box-shadow: 0 8px 20px rgba(26,60,94,.1); }
        .why-check {
            width: 32px; height: 32px; border-radius: 8px; flex-shrink: 0; margin-top: 1px;
            background: linear-gradient(135deg, var(--p), var(--a));
            display: flex; align-items: center; justify-content: center;
        }
        .why-check svg { color: #fff; }
        .why-item h5 { font-size: .92rem; font-weight: 700; color: var(--p); margin-bottom: .22rem; }
        .why-item p  { font-size: .83rem; color: #6b7280; line-height: 1.6; margin: 0; }

        /* ── CTA strip ─────────────────────────────────────────────── */
        .cta-strip {
            background: linear-gradient(105deg, var(--p), var(--a));
            border-radius: 16px; padding: 2.5rem 2rem; text-align: center;
            box-shadow: 0 8px 32px rgba(26,60,94,.2);
        }
        .cta-strip h3 { color: #fff; font-size: 1.5rem; font-weight: 800; margin-bottom: .5rem; }
        .cta-strip p  { color: rgba(255,255,255,.7); font-size: .95rem; line-height: 1.65; margin-bottom: 1.5rem; }
        .strip-btns { display: flex; justify-content: center; gap: .85rem; flex-wrap: wrap; }
        .btn-white {
            display: inline-flex; align-items: center; gap: .5rem;
            background: #fff; color: var(--p);
            text-decoration: none; font-size: .88rem; font-weight: 800;
            padding: .72rem 1.6rem; border-radius: 9px; letter-spacing: .02em;
            box-shadow: 0 4px 14px rgba(0,0,0,.15);
            transition: transform .18s, box-shadow .18s;
        }
        .btn-white:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(0,0,0,.2); }
        .btn-ghost {
            display: inline-flex; align-items: center; gap: .5rem;
            background: rgba(255,255,255,.12); color: #fff;
            text-decoration: none; font-size: .88rem; font-weight: 700;
            padding: .72rem 1.6rem; border-radius: 9px; letter-spacing: .02em;
            border: 1.5px solid rgba(255,255,255,.3);
            transition: background .18s, border-color .18s;
        }
        .btn-ghost:hover { background: rgba(255,255,255,.22); border-color: rgba(255,255,255,.5); }

        /* ── Footer ─────────────────────────────────────────────────── */
        footer { background: var(--p); color: rgba(255,255,255,.42); text-align: center; padding: 1.75rem 2rem; font-size: .82rem; }
        footer .foot-links { margin-top: .4rem; }
        footer a { color: rgba(255,255,255,.62); text-decoration: none; margin: 0 .5rem; }
        footer a:hover { color: #fff; }

        @media (max-width: 760px) {
            .intro-section { grid-template-columns: 1fr; }
            .page-hero h1 { font-size: 2rem; }
        }
    </style>
</head>
<body>

@include('public._nav')

<section class="page-hero">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <svg class="hero-net" viewBox="0 0 1200 280" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
        <circle cx="80"  cy="40"  r="3.5" fill="white" style="animation:node-pulse 3.2s ease-in-out infinite 0s"/>
        <circle cx="280" cy="110" r="4.5" fill="white" style="animation:node-pulse 3.2s ease-in-out infinite .7s"/>
        <circle cx="500" cy="55"  r="3"   fill="white" style="animation:node-pulse 3.2s ease-in-out infinite 1.3s"/>
        <circle cx="740" cy="140" r="4"   fill="white" style="animation:node-pulse 3.2s ease-in-out infinite .4s"/>
        <circle cx="960" cy="65"  r="3.5" fill="white" style="animation:node-pulse 3.2s ease-in-out infinite 1.8s"/>
        <circle cx="1130" cy="145" r="3"  fill="white" style="animation:node-pulse 3.2s ease-in-out infinite .9s"/>
        <circle cx="400" cy="210" r="3.5" fill="white" style="animation:node-pulse 3.2s ease-in-out infinite 1.5s"/>
        <circle cx="850" cy="225" r="4"   fill="white" style="animation:node-pulse 3.2s ease-in-out infinite 2.1s"/>
        <line x1="80"  y1="40"  x2="280" y2="110" stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite .2s"/>
        <line x1="280" y1="110" x2="500" y2="55"  stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite .9s"/>
        <line x1="500" y1="55"  x2="740" y2="140" stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite .5s"/>
        <line x1="740" y1="140" x2="960" y2="65"  stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite 1.2s"/>
        <line x1="960" y1="65"  x2="1130" y2="145" stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite .4s"/>
        <line x1="280" y1="110" x2="400" y2="210" stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite 1.6s"/>
        <line x1="740" y1="140" x2="850" y2="225" stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite 1s"/>
        <line x1="400" y1="210" x2="850" y2="225" stroke="white" stroke-width=".8" style="animation:line-fade 4s ease-in-out infinite 2s"/>
    </svg>

    <div class="page-hero-inner">
        <div class="h-eyebrow">
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            Our Company
        </div>
        <h1>About <span>DataTel</span></h1>
        <p>A trusted partner for data communications and cabling infrastructure — built on expertise, reliability, and a commitment to quality.</p>
    </div>
</section>

<div class="page-body">

    {{-- Who we are --}}
    <div class="intro-section" id="who-we-are">
        <div class="intro-text">
            <div class="sec-eyebrow">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3"/></svg>
                Who We Are
            </div>
            <h2>Full-Service Data Communications</h2>
            <p>DataTel is a full-service data communications and cabling company serving commercial, industrial, and institutional clients across the region. We design, install, and certify structured cabling systems, fiber optic networks, wireless infrastructure, and more.</p>
            <p>Our team of certified technicians brings hands-on expertise to every project — whether it's a single office drop or a multi-building campus infrastructure build.</p>
            <p><a href="{{ route('services') }}">View all our services →</a></p>
        </div>
        <div class="intro-visual">
            <div class="mission-label">Our Mission</div>
            <div class="mission-text">To deliver reliable, standards-compliant network infrastructure on time and on budget — backed by experienced technicians and a commitment to quality workmanship.</div>
            <hr class="mission-divider">
            <ul class="check-list">
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Licensed, bonded, and fully insured
                </li>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Industry-certified cabling installations
                </li>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Transparent pricing with no surprises
                </li>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Real-time work order tracking for clients
                </li>
            </ul>
        </div>
    </div>

    {{-- Values --}}
    <div class="section-block" id="values">
        <div class="sec-eyebrow">
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            Core Values
        </div>
        <h2>What Drives Everything We Do</h2>
        <p>These principles aren't just words on a wall — they shape every installation, every service call, and every customer interaction.</p>
        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </div>
                <h4>Quality</h4>
                <p>Every installation is tested and certified to industry standards. We don't cut corners and we don't leave until the job is right.</p>
            </div>
            <div class="value-card">
                <div class="value-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h4>Reliability</h4>
                <p>We show up on time, keep you informed, and finish what we start. Your schedule and your uptime matter to us.</p>
            </div>
            <div class="value-card">
                <div class="value-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h4>Transparency</h4>
                <p>Clear pricing, honest timelines, no surprises on invoices. You can track your work orders in real time through our client portal.</p>
            </div>
            <div class="value-card">
                <div class="value-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h4>Expertise</h4>
                <p>Certified technicians with years of hands-on field experience. We stay current with evolving standards and technologies so you don't have to.</p>
            </div>
        </div>
    </div>

    {{-- Why choose DataTel --}}
    <div class="section-block" id="why-us">
        <div class="sec-eyebrow">
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
            Why DataTel
        </div>
        <h2>What Sets Us Apart</h2>
        <p>We combine technical expertise with a client-first approach and modern tools that keep you in the loop from first call to final sign-off.</p>
        <div class="why-grid">
            <div class="why-item">
                <div class="why-check"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
                <div><h5>Certified Installations</h5><p>All cabling installations are tested and certified to applicable TIA/EIA standards, with documentation provided.</p></div>
            </div>
            <div class="why-item">
                <div class="why-check"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
                <div><h5>Client Portal Access</h5><p>Customers get real-time access to work order status, technician notes, photos, and invoices through our web portal.</p></div>
            </div>
            <div class="why-item">
                <div class="why-check"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
                <div><h5>Rapid Response</h5><p>Emergency network issues don't wait. Our team prioritizes urgent calls and offers after-hours support when you need it most.</p></div>
            </div>
            <div class="why-item">
                <div class="why-check"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
                <div><h5>End-to-End Capability</h5><p>From initial design and cable pulls to patch panel termination, testing, and documentation — we handle the full scope.</p></div>
            </div>
            <div class="why-item">
                <div class="why-check"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
                <div><h5>Transparent Invoicing</h5><p>Itemized invoices with digital sign-off. No mystery charges, no vague line items. Review and approve online before you pay.</p></div>
            </div>
            <div class="why-item">
                <div class="why-check"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
                <div><h5>Scalable Solutions</h5><p>Whether you're a small office or a multi-site enterprise, our solutions scale to your needs without over-engineering.</p></div>
            </div>
        </div>
    </div>

    {{-- CTA strip --}}
    <div class="cta-strip">
        <h3>Ready to get started?</h3>
        <p>Create a free customer account to submit work orders and track progress, or send us a message to discuss your project.</p>
        <div class="strip-btns">
            <a href="{{ route('register') }}" class="btn-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Create an Account
            </a>
            <a href="{{ route('contact') }}" class="btn-ghost">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                Contact Us
            </a>
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
    var items = document.querySelectorAll('.value-card, .why-item');
    if (!items.length || !window.IntersectionObserver) { items.forEach(function(el){ el.classList.add('in'); }); return; }
    var obs = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            var idx = Array.from(items).indexOf(entry.target);
            setTimeout(function () { entry.target.classList.add('in'); }, (idx % 6) * 80);
            obs.unobserve(entry.target);
        });
    }, { threshold: 0.1 });
    items.forEach(function (el) { obs.observe(el); });
})();
</script>

</body>
</html>
